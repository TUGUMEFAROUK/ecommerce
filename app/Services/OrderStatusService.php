<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /**
     * Allowed next statuses per current status. Terminal statuses allow
     * no transitions at all.
     *
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        'pending' => ['paid', 'processing', 'cancelled'],
        'paid' => ['processing', 'cancelled', 'refunded'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    /**
     * The statuses an order may move to from its current status.
     *
     * @return list<string>
     */
    public function allowedTransitions(OrderStatus $current): array
    {
        return self::TRANSITIONS[$current->value] ?? [];
    }

    /**
     * Move an order to a new status and record the change.
     *
     * The transition is validated here rather than in the request so the
     * rules cannot drift from what the UI offers; invalid moves abort with
     * a validation error and leave the order untouched. The actor is
     * optional: webhook-driven transitions have no human behind them and
     * are recorded as system changes.
     *
     * Cancelling an order returns its reserved stock to the products it
     * was placed for, so a cancelled order never keeps inventory out of
     * the store. Every returned unit is written as an auditable movement.
     */
    public function transition(Order $order, OrderStatus $to, ?string $notes, ?User $actor = null): Order
    {
        $from = $order->status;

        if ($from === $to) {
            throw ValidationException::withMessages([
                'status' => sprintf('The order is already %s.', Str::headline($to->value)),
            ]);
        }

        if (! in_array($to->value, $this->allowedTransitions($from), true)) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'An order cannot move from %s to %s.',
                    Str::headline($from->value),
                    Str::headline($to->value),
                ),
            ]);
        }

        DB::transaction(function () use ($order, $from, $to, $notes, $actor): void {
            $order->update(['status' => $to]);

            $order->statusHistory()->create([
                'user_id' => $actor?->getKey(),
                'from_status' => $from->value,
                'to_status' => $to->value,
                'notes' => $notes,
            ]);

            if ($to === OrderStatus::Cancelled) {
                $this->restoreStock($order, $actor);
            }
        });

        return $order;
    }

    /**
     * Return the units reserved by a cancelled order to their products.
     *
     * The products are re-read under a row lock so a concurrent order
     * cannot cause the same unit to be handed out twice.
     */
    private function restoreStock(Order $order, ?User $actor): void
    {
        $inventory = app(InventoryService::class);

        foreach ($order->items()->get() as $item) {
            if ($item->product_id === null) {
                continue;
            }

            $product = Product::query()
                ->lockForUpdate()
                ->find($item->product_id);

            if ($product === null) {
                continue;
            }

            $inventory->adjustStock(
                $product,
                (int) $product->stock_quantity + (int) $item->quantity,
                $actor?->getKey(),
                sprintf('Order #%d cancelled', $order->getKey()),
            );
        }
    }
}
