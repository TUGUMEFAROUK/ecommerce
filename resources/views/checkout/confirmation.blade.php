<x-layouts.shop :title="'Order #'.$order->id.' confirmed'">
    <div class="shell pt-10 lg:pt-14">
        <div class="rounded-sm border border-olive/25 bg-olive/10 px-6 py-10 text-center">
            <p class="eyebrow">Order confirmed</p>
            <h1 class="mt-4 font-display text-3xl sm:text-4xl">Thank you! Your order has been placed.</h1>
            <p class="mt-3 text-sm text-charcoal/70">
                Order number <span class="font-medium text-charcoal">#{{ $order->id }}</span>
            </p>
        </div>

        <div class="surface mt-8 p-6 sm:p-9">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl">Order #{{ $order->id }}</h2>
                    <p class="mt-1 text-sm text-charcoal/55">
                        Placed on {{ $order->created_at?->format('M j, Y H:i') }}
                    </p>
                </div>
                <span class="rounded-sm border border-sand px-3 py-1.5 text-[0.6875rem] uppercase tracking-[0.16em] capitalize text-charcoal">
                    {{ $order->status->value }}
                </span>
            </div>

            @php($payment = $order->payments->first())

            @if ($payment !== null && $payment->provider === 'flutterwave')
                <div class="mt-6 rounded-sm border border-sand bg-mist p-5 text-sm">
                    <p class="font-medium text-charcoal">Payment: Card &amp; online payments (Flutterwave)</p>

                    @if ($payment->status === \App\Enums\PaymentStatus::Succeeded)
                        <p class="mt-1 text-charcoal/70">
                            Paid {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}
                            @if ($payment->paid_at !== null)
                                on {{ $payment->paid_at->format('M j, Y H:i') }}
                            @endif.
                        </p>
                    @elseif ($payment->status === \App\Enums\PaymentStatus::Failed)
                        <p class="mt-1 text-clay">
                            Your payment did not complete. You can try again below.
                        </p>
                        <form method="POST" action="{{ route('checkout.pay', $order) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">Complete payment</button>
                        </form>
                    @else
                        <p class="mt-1 text-charcoal/70">
                            Payment of {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }} is still pending.
                            If you have not finished paying yet, use the button below to return to the payment page.
                        </p>
                        <form method="POST" action="{{ route('checkout.pay', $order) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">Complete payment</button>
                        </form>
                    @endif
                </div>
            @else
                <div class="mt-6 rounded-sm border border-sand bg-mist p-5 text-sm">
                    <p class="font-medium text-charcoal">Payment: Cash on delivery</p>
                    <p class="mt-1 text-charcoal/70">
                        Pay {{ $order->currency }} {{ number_format((float) $order->total, 2) }} when your order arrives.
                        @if ($payment !== null)
                            Payment status: <span class="capitalize">{{ $payment->status->value }}</span>.
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-charcoal/50">
                        No online payment has been taken for this order.
                    </p>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="mt-8 w-full min-w-[640px] text-left text-sm">
                    <caption class="sr-only">Ordered items</caption>
                    <thead class="border-b border-sand text-[0.625rem] uppercase tracking-[0.16em] text-charcoal/50">
                        <tr>
                            <th scope="col" class="py-3 pr-4 font-medium">Product</th>
                            <th scope="col" class="py-3 pr-4 font-medium">Unit price</th>
                            <th scope="col" class="py-3 pr-4 font-medium">Quantity</th>
                            <th scope="col" class="py-3 font-medium">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b border-sand/60">
                                <th scope="row" class="py-3 pr-4 font-medium text-charcoal">{{ $item->product_name }}</th>
                                <td class="py-3 pr-4 text-charcoal/70">{{ $order->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="py-3 pr-4 text-charcoal/70">{{ $item->quantity }}</td>
                                <td class="py-3 text-charcoal/70">{{ $order->currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" colspan="3" class="py-4 pr-4 text-right font-display text-lg">Total</th>
                            <td class="py-4 font-medium">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-8 grid gap-6 border-t border-sand pt-8 text-sm sm:grid-cols-2">
                <div>
                    <h3 class="eyebrow">Contact</h3>
                    <p class="mt-2 text-charcoal/75">{{ $order->contact_name }}</p>
                    <p class="text-charcoal/75">{{ $order->contact_email }}</p>
                    <p class="text-charcoal/75">{{ $order->contact_phone }}</p>
                </div>

                <div>
                    <h3 class="eyebrow">Shipping to</h3>
                    <p class="mt-2 text-charcoal/75">{{ $order->shipping_line1 }}</p>
                    @if (filled($order->shipping_line2))
                        <p class="text-charcoal/75">{{ $order->shipping_line2 }}</p>
                    @endif
                    <p class="text-charcoal/75">
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}
                    </p>
                    <p class="text-charcoal/75">{{ $order->shipping_country }}</p>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('account.orders.show', $order) }}" class="btn btn-primary">View order details</a>
                <a href="{{ route('products.index') }}" class="btn btn-outline">Continue shopping</a>
            </div>
        </div>
    </div>
</x-layouts.shop>
