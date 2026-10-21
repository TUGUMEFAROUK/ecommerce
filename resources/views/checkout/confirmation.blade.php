<x-layouts.app :title="'Order #'.$order->id.' confirmed'">
    <div class="rounded border border-green-200 bg-green-50 px-6 py-8 text-center">
        <h1 class="text-xl font-semibold text-green-900">Thank you! Your order has been placed.</h1>
        <p class="mt-2 text-sm text-green-800">
            Order number <span class="font-semibold">#{{ $order->id }}</span>
        </p>
    </div>

    <div class="mt-6 rounded border border-gray-200 bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Order #{{ $order->id }}</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Placed on {{ $order->created_at?->format('M j, Y H:i') }}
                </p>
            </div>
            <span class="rounded bg-gray-100 px-3 py-1 text-sm capitalize">{{ $order->status->value }}</span>
        </div>

        @php($payment = $order->payments->first())

        @if ($payment !== null && $payment->provider === 'flutterwave')
            <div class="mt-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm">
                <p class="font-medium text-gray-900">Payment: Card &amp; online payments (Flutterwave)</p>

                @if ($payment->status === \App\Enums\PaymentStatus::Succeeded)
                    <p class="mt-1 text-gray-600">
                        Paid {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}
                        @if ($payment->paid_at !== null)
                            on {{ $payment->paid_at->format('M j, Y H:i') }}
                        @endif.
                    </p>
                @elseif ($payment->status === \App\Enums\PaymentStatus::Failed)
                    <p class="mt-1 text-red-700">
                        Your payment did not complete. You can try again below.
                    </p>
                    <form method="POST" action="{{ route('checkout.pay', $order) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                            Complete payment
                        </button>
                    </form>
                @else
                    <p class="mt-1 text-gray-600">
                        Payment of {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }} is still pending.
                        If you have not finished paying yet, use the button below to return to the payment page.
                    </p>
                    <form method="POST" action="{{ route('checkout.pay', $order) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                            Complete payment
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="mt-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm">
                <p class="font-medium text-gray-900">Payment: Cash on delivery</p>
                <p class="mt-1 text-gray-600">
                    Pay {{ $order->currency }} {{ number_format((float) $order->total, 2) }} when your order arrives.
                    @if ($payment !== null)
                        Payment status: <span class="capitalize">{{ $payment->status->value }}</span>.
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    No online payment has been taken for this order.
                </p>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="mt-6 w-full min-w-[640px] text-left text-sm">
                <caption class="sr-only">Ordered items</caption>
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th scope="col" class="py-2 pr-4 font-medium">Product</th>
                        <th scope="col" class="py-2 pr-4 font-medium">Unit price</th>
                        <th scope="col" class="py-2 pr-4 font-medium">Quantity</th>
                        <th scope="col" class="py-2 font-medium">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-gray-100">
                            <th scope="row" class="py-2 pr-4 font-medium text-gray-900">{{ $item->product_name }}</th>
                            <td class="py-2 pr-4">{{ $order->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="py-2 pr-4">{{ $item->quantity }}</td>
                            <td class="py-2">{{ $order->currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" colspan="3" class="py-3 pr-4 text-right font-semibold">Total</th>
                        <td class="py-3 font-semibold">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 grid gap-4 border-t border-gray-200 pt-6 text-sm sm:grid-cols-2">
            <div>
                <h3 class="font-medium text-gray-900">Contact</h3>
                <p class="mt-1 text-gray-600">{{ $order->contact_name }}</p>
                <p class="text-gray-600">{{ $order->contact_email }}</p>
                <p class="text-gray-600">{{ $order->contact_phone }}</p>
            </div>

            <div>
                <h3 class="font-medium text-gray-900">Shipping to</h3>
                <p class="mt-1 text-gray-600">{{ $order->shipping_line1 }}</p>
                @if (filled($order->shipping_line2))
                    <p class="text-gray-600">{{ $order->shipping_line2 }}</p>
                @endif
                <p class="text-gray-600">
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}
                </p>
                <p class="text-gray-600">{{ $order->shipping_country }}</p>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-4">
            <a href="{{ route('account.orders.show', $order) }}" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                View order details
            </a>
            <a href="{{ route('products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                Continue shopping
            </a>
        </div>
    </div>
</x-layouts.app>
