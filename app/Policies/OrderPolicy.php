<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     *
     * Customers may only see their own orders. Orders of other customers
     * are reported as missing (404) so order IDs cannot be enumerated.
     */
    public function view(User $user, Order $order): Response
    {
        return $user->id === $order->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can change the order's status.
     *
     * Status transitions are an administrative action; customers are
     * refused outright (403), since they already know their order exists.
     */
    public function update(User $user, Order $order): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny();
    }
}
