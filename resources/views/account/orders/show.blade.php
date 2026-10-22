<x-layouts.shop :title="'Order #'.$order->id">
    <div class="shell pt-10 lg:pt-14">
        <nav aria-label="Breadcrumb" class="mb-8">
            <ol class="flex flex-wrap items-center gap-2 text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal/55">
                <li><a href="{{ route('account.show') }}" class="transition-colors duration-200 hover:text-olive">Account</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-charcoal">Order #{{ $order->id }}</li>
            </ol>
        </nav>

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Order</p>
                <h1 class="mt-3 font-display text-4xl sm:text-5xl">Order #{{ $order->id }}</h1>
                <p class="mt-3 text-sm text-charcoal/55">
                    Placed on {{ $order->created_at?->format('M j, Y') }}
                </p>
            </div>

            <span class="rounded-sm border border-sand bg-white px-3 py-1.5 text-[0.6875rem] uppercase tracking-[0.16em] capitalize text-charcoal">
                {{ $order->status->value }}
            </span>
        </div>

        <section class="surface mt-10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <caption class="sr-only">Order items</caption>
                    <thead class="border-b border-sand text-[0.625rem] uppercase tracking-[0.16em] text-charcoal/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">Product</th>
                            <th scope="col" class="px-6 py-3 font-medium">SKU</th>
                            <th scope="col" class="px-6 py-3 font-medium">Unit price</th>
                            <th scope="col" class="px-6 py-3 font-medium">Quantity</th>
                            <th scope="col" class="px-6 py-3 font-medium">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b border-sand/60">
                                <th scope="row" class="px-6 py-4 font-medium text-charcoal">{{ $item->product_name }}</th>
                                <td class="px-6 py-4 text-charcoal/70">{{ $item->product_sku }}</td>
                                <td class="px-6 py-4 text-charcoal/70">{{ $order->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 text-charcoal/70">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-charcoal">{{ $order->currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" colspan="4" class="px-6 py-4 text-right font-display text-lg">Total</th>
                            <td class="px-6 py-4 font-medium">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        @if ($order->payments->isNotEmpty())
            <section class="mt-10">
                <h2 class="font-display text-2xl">Payments</h2>

                <div class="surface mt-4 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-left text-sm">
                            <caption class="sr-only">Order payments</caption>
                            <thead class="border-b border-sand text-[0.625rem] uppercase tracking-[0.16em] text-charcoal/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Provider</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Reference</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Amount</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Paid at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->payments as $payment)
                                    <tr class="border-b border-sand/60">
                                        <th scope="row" class="px-6 py-4 font-medium capitalize text-charcoal">{{ $payment->provider }}</th>
                                        <td class="px-6 py-4 text-charcoal/70">{{ $payment->provider_reference }}</td>
                                        <td class="px-6 py-4 text-charcoal/70">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="px-6 py-4 capitalize text-charcoal/70">{{ $payment->status->value }}</td>
                                        <td class="px-6 py-4 text-charcoal/70">{{ $payment->paid_at?->format('M j, Y H:i') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <div class="mt-10">
            <a href="{{ route('account.show') }}" class="link-quiet text-sm">
                &larr; Back to my account
            </a>
        </div>
    </div>
</x-layouts.shop>
