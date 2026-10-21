<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountOrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the details of an order belonging to the authenticated customer.
     */
    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        return view('account.orders.show', [
            'order' => $order->load(['items', 'payments']),
        ]);
    }
}
