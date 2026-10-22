<x-layouts.admin title="Admin dashboard">
    @php($settings = app(\App\Services\SettingsService::class))
    @php($currency = $settings->get('store_currency'))

    <x-admin.page-header title="Dashboard" description="A live overview of sales, customers and inventory.">
        <p class="text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</p>
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin.stat-card label="Orders" :value="number_format($total_orders)" icon="orders" meta="All time" />
        <x-admin.stat-card
            label="Recognized revenue"
            :value="\App\Support\Money::formatWithCode($total_sales, $currency)"
            icon="currency"
            meta="Paid, processing, shipped and delivered"
        />
        <x-admin.stat-card
            label="Revenue this month"
            :value="\App\Support\Money::formatWithCode($month_sales, $currency)"
            icon="calendar"
            :meta="now()->format('F Y')"
        />
        <x-admin.stat-card label="Customers" :value="number_format($customers)" icon="users" meta="Registered customers" />
        <x-admin.stat-card label="Products" :value="number_format($products)" icon="box" meta="Total catalogue" />
    </div>

    @if ($low_stock_count > 0)
        <div class="mb-6 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <div>
                    <p class="font-medium">{{ $low_stock_count }} {{ Str::plural('product', $low_stock_count) }} at or below the low-stock threshold.</p>
                    <p class="text-amber-800/80">Restock before these sell out.</p>
                </div>
            </div>
            <x-admin.button variant="secondary" size="sm" :href="route('admin.inventory.index')">Review inventory</x-admin.button>
        </div>
    @endif

    <div class="mb-6 grid gap-6 xl:grid-cols-3">
        <section class="admin-card xl:col-span-2">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Revenue</h2>
                    <p class="text-sm text-slate-500">
                        {{ \App\Support\Money::formatWithCode($series_total, $currency) }} from {{ number_format($series_orders) }} {{ Str::plural('order', $series_orders) }}
                        in the last {{ $range }} {{ Str::plural('day', $range) }}.
                    </p>
                </div>

                <div class="flex items-center gap-1 rounded-lg bg-slate-100 p-1" role="group" aria-label="Chart date range">
                    @foreach ($ranges as $option)
                        <a
                            href="{{ route('admin.dashboard', ['range' => $option]) }}"
                            @if ($range === $option) aria-current="page" @endif
                            class="rounded-md px-3 py-1 text-xs font-medium transition-colors {{ $range === $option ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            {{ $option }}d
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($series_total > 0)
                <x-admin.sales-chart :series="$series" :max="$series_max" :currency="$currency" :total="$series_total" :orders="$series_orders" :range="$range" />
            @else
                <x-admin.empty-state
                    title="No recognized revenue in this period"
                    message="Revenue appears here once orders reach a paid, processing, shipped or delivered status."
                />
            @endif
        </section>

        <section class="admin-card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Top selling products</h2>
                    <p class="text-sm text-slate-500">By units on recognized sales.</p>
                </div>
            </div>

            @if (empty($top_products))
                <div class="flex flex-col items-center gap-2 px-6 py-12 text-center">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9L12 21.75M3 7.5l9 5.25M3 7.5v9L12 21.75m0-9.5v9.5" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <p class="text-sm font-medium text-slate-900">No sales yet</p>
                    <p class="text-sm text-slate-500">Your best sellers are listed here once orders are recognized.</p>
                </div>
            @else
                <ol class="divide-y divide-slate-100">
                    @foreach ($top_products as $item)
                        <li class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                @if ($item['product'])
                                    <a href="{{ route('admin.products.edit', $item['product']) }}" class="truncate text-sm font-medium text-slate-900 hover:underline">{{ $item['name'] }}</a>
                                @else
                                    <span class="truncate text-sm font-medium text-slate-900">{{ $item['name'] }}</span>
                                @endif
                                <p class="text-xs text-slate-500">{{ number_format($item['units']) }} {{ Str::plural('unit', $item['units']) }} sold</p>
                            </div>
                            <span class="shrink-0 text-sm font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($item['revenue'], $currency) }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    <div class="mb-6 grid gap-6 xl:grid-cols-3">
        <section class="admin-card xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Recent orders</h2>
                    <p class="text-sm text-slate-500">The latest activity across the store.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View all orders</a>
            </div>

            @if ($recent_orders->isEmpty())
                <x-admin.empty-state
                    title="No orders yet"
                    message="Orders appear here as soon as customers complete checkout."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="admin-th">Order</th>
                                <th class="admin-th">Customer</th>
                                <th class="admin-th">Placed</th>
                                <th class="admin-th">Status</th>
                                <th class="admin-th text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent_orders as $order)
                                <tr class="admin-row">
                                    <td class="admin-td">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-slate-900 hover:underline">#{{ $order->id }}</a>
                                    </td>
                                    <td class="admin-td">{{ $order->user?->name ?? $order->contact_name ?? '—' }}</td>
                                    <td class="admin-td whitespace-nowrap">{{ $order->created_at?->format('M j, Y H:i') }}</td>
                                    <td class="admin-td">
                                        <x-admin.status-badge :status="$order->status" />
                                    </td>
                                    <td class="admin-td whitespace-nowrap text-right font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($order->total, $order->currency) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="flex flex-col gap-6">
            <section class="admin-card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Orders by status</h2>
                    <p class="text-sm text-slate-500">A breakdown of every order.</p>
                </div>
                <dl class="divide-y divide-slate-100">
                    @foreach ($orders_by_status as $status => $count)
                        <div class="flex items-center justify-between px-5 py-3 text-sm">
                            <dt class="text-slate-600">{{ Str::headline($status) }}</dt>
                            <dd class="font-medium text-slate-900">{{ number_format($count) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="admin-card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Low stock</h2>
                        <p class="text-sm text-slate-500">Active products at or below the threshold.</p>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Inventory</a>
                </div>

                @if ($low_stock_products->isEmpty())
                    <div class="flex flex-col items-center gap-2 px-6 py-10 text-center">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <p class="text-sm font-medium text-slate-900">All stocked up</p>
                        <p class="text-sm text-slate-500">No active product is currently low on stock.</p>
                    </div>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($low_stock_products as $product)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="truncate text-sm font-medium text-slate-900 hover:underline">{{ $product->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $product->sku }}</p>
                                </div>
                                <x-admin.badge tone="amber">{{ $product->stock_quantity }} left</x-admin.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-layouts.admin>
