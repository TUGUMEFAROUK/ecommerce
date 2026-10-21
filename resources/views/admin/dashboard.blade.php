<x-layouts.admin title="Admin dashboard">
    <x-admin.page-header title="Admin dashboard" />

    @php($settings = app(\App\Services\SettingsService::class))

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-admin.stat-card label="Orders" :value="$total_orders" />
        <x-admin.stat-card
            label="Sales"
            :value="$settings->get('store_currency').' '.number_format($total_sales, 2)"
            meta="Paid, processing, shipped and delivered"
        />
        <x-admin.stat-card
            label="This month"
            :value="$settings->get('store_currency').' '.number_format($month_sales, 2)"
            :meta="now()->format('F Y')"
        />
        <x-admin.stat-card label="Customers" :value="$customers" />
        <x-admin.stat-card label="Products" :value="$products" />
    </div>

    @if ($low_stock_count > 0)
        <div class="mb-8 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">
            {{ $low_stock_count }} {{ Str::plural('product', $low_stock_count) }} at or below the low-stock threshold.
            <a href="{{ route('admin.inventory.index') }}" class="font-semibold underline">Review inventory</a>
        </div>
    @endif

    <div class="mb-8 grid gap-4 lg:grid-cols-2">
        <section class="rounded border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold">Orders by status</h2>

            <dl class="divide-y divide-gray-100">
                @foreach ($orders_by_status as $status => $count)
                    <div class="flex items-center justify-between py-2 text-sm">
                        <dt class="text-gray-600">{{ Str::headline($status) }}</dt>
                        <dd class="font-semibold">{{ $count }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="rounded border border-gray-200 bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Low stock</h2>
                <a href="{{ route('admin.inventory.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">All inventory</a>
            </div>

            @if ($low_stock_products->isEmpty())
                <p class="text-sm text-gray-600">No active products are low on stock.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($low_stock_products as $product)
                        <li class="flex items-center justify-between py-2 text-sm">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-gray-900 hover:underline">{{ $product->name }}</a>
                            <span class="font-semibold text-amber-700">{{ $product->stock_quantity }} left</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="rounded border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">All orders</a>
        </div>

        @if ($recent_orders->isEmpty())
            <p class="text-sm text-gray-600">No orders have been placed yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 pr-4 font-medium">Order</th>
                            <th class="py-2 pr-4 font-medium">Customer</th>
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent_orders as $order)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">#{{ $order->id }}</a>
                                </td>
                                <td class="py-2 pr-4">{{ $order->user?->name ?? $order->contact_name ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ $order->created_at?->format('M j, Y') }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $order->status->value }}</td>
                                <td class="py-2">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
