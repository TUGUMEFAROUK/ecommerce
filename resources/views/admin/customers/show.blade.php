<x-layouts.admin :title="$customer->name">
    <x-admin.page-header :title="$customer->name" :description="$customer->email">
        <x-admin.button variant="secondary" :href="route('admin.customers.index')">Back to customers</x-admin.button>
    </x-admin.page-header>

    @php($currency = app(\App\Services\SettingsService::class)->get('store_currency'))

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Email" :value="$customer->email" icon="users" />
        <x-admin.stat-card label="Joined" :value="$customer->created_at?->format('M j, Y') ?? '—'" icon="calendar" />
        <x-admin.stat-card label="Orders" :value="number_format($orderCount)" icon="orders" />
        <x-admin.stat-card
            label="Total spent"
            :value="\App\Support\Money::formatWithCode($totalSpent, $currency)"
            icon="currency"
            meta="Recognized sales only"
        />
    </div>

    <section class="admin-card">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Recent orders</h2>
                <p class="text-sm text-slate-500">The latest orders placed by this customer.</p>
            </div>
            <a href="{{ route('admin.orders.index', ['search' => $customer->email]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                Search all orders
            </a>
        </div>

        @if ($orders->isEmpty())
            <x-admin.empty-state
                title="No orders yet"
                message="This customer has not placed any orders yet."
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="admin-th">Order</th>
                            <th class="admin-th">Date</th>
                            <th class="admin-th">Status</th>
                            <th class="admin-th text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="admin-row">
                                <td class="admin-td">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-slate-900 hover:underline">#{{ $order->id }}</a>
                                </td>
                                <td class="admin-td whitespace-nowrap">{{ $order->created_at?->format('M j, Y') }}</td>
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
</x-layouts.admin>
