<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * List orders with search and status filters.
     */
    public function index(Request $request): View
    {
        $query = Order::query()->with('user')->latest();

        $search = trim((string) $request->query('search'));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function (Builder $builder) use ($like, $search) {
                $builder
                    ->where('id', $search)
                    ->orWhereHas('user', fn (Builder $user) => $user
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like))
                    ->orWhere('contact_email', 'like', $like);
            });
        }

        $status = OrderStatus::tryFrom((string) $request->query('status'));

        if ($status !== null) {
            $query->where('status', $status);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'statuses' => OrderStatus::cases(),
        ]);
    }

    /**
     * Show an order with its items, payment, and status history.
     */
    public function show(Order $order, OrderStatusService $statuses): View
    {
        $this->authorize('view', $order);

        return view('admin.orders.show', [
            'order' => $order->load(['items', 'payments', 'user', 'statusHistory.user']),
            'allowedTransitions' => $statuses->allowedTransitions($order->status),
        ]);
    }

    /**
     * Move an order to the next status and record the change.
     *
     * OrderPolicy::update restricts this to administrators; the transition
     * itself is validated by OrderStatusService so the allowed moves
     * cannot drift from the UI.
     */
    public function updateStatus(AdminOrderStatusRequest $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validated();

        $statuses->transition(
            $order,
            OrderStatus::from($validated['status']),
            $validated['notes'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Order status updated.');
    }
}
