<x-layouts.app :title="'Order #'.$order->id">
    <div class="rounded border border-gray-200 bg-white p-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Order #{{ $order->id }}</h1>
            <span class="rounded bg-gray-100 px-3 py-1 text-sm capitalize">{{ $order->status->value }}</span>
        </div>

        <p class="mb-6 text-sm text-gray-600">
            Placed on {{ $order->created_at?->format('M j, Y') }}
        </p>

        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-600">
                <tr>
                    <th class="py-2 pr-4 font-medium">Product</th>
                    <th class="py-2 pr-4 font-medium">SKU</th>
                    <th class="py-2 pr-4 font-medium">Unit price</th>
                    <th class="py-2 pr-4 font-medium">Quantity</th>
                    <th class="py-2 font-medium">Line total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 pr-4">{{ $item->product_name }}</td>
                        <td class="py-2 pr-4">{{ $item->product_sku }}</td>
                        <td class="py-2 pr-4">{{ $order->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="py-2 pr-4">{{ $item->quantity }}</td>
                        <td class="py-2">{{ $order->currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="py-3 pr-4 text-right font-semibold">Total</td>
                    <td class="py-3 font-semibold">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($order->payments->isNotEmpty())
            <h2 class="mb-3 mt-8 text-lg font-semibold">Payments</h2>

            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Provider</th>
                        <th class="py-2 pr-4 font-medium">Reference</th>
                        <th class="py-2 pr-4 font-medium">Amount</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 font-medium">Paid at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->payments as $payment)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-4 capitalize">{{ $payment->provider }}</td>
                            <td class="py-2 pr-4">{{ $payment->provider_reference }}</td>
                            <td class="py-2 pr-4">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="py-2 pr-4 capitalize">{{ $payment->status->value }}</td>
                            <td class="py-2">{{ $payment->paid_at?->format('M j, Y H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="mt-8">
            <a href="{{ route('account.show') }}" class="text-sm text-gray-900 underline">
                &larr; Back to my account
            </a>
        </div>
    </div>
</x-layouts.app>
