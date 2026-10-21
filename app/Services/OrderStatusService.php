<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
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
        });

        return $order;
    }
}
