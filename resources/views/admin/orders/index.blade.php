<x-layouts.admin title="Orders">
    <x-admin.page-header title="Orders" description="Every order placed in the store, with its payment state." />

    <form
        method="GET"
        action="{{ route('admin.orders.index') }}"
        class="admin-card mb-6 flex flex-wrap items-end gap-3 p-4"
        role="search"
    >
        <div class="min-w-48 flex-1">
            <label for="filter-search" class="admin-label">Search</label>
            <input
                id="filter-search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                maxlength="100"
                placeholder="Order number, customer name or email"
                class="admin-field"
            >
        </div>

        <div>
            <label for="filter-status" class="admin-label">Status</label>
            <select id="filter-status" name="status" class="admin-field">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ Str::headline($status->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <x-admin.button type="submit">Filter</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.orders.index')">Reset</x-admin.button>
        </div>
    </form>

    @if ($orders->isEmpty())
        <x-admin.empty-state
            title="No orders found"
            message="Orders appear here as soon as customers complete checkout."
        />
    @else
        <div class="admin-table-wrap">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="admin-th">Order</th>
                        <th class="admin-th">Customer</th>
                        <th class="admin-th">Date</th>
                        <th class="admin-th">Status</th>
                        <th class="admin-th">Payment</th>
                        <th class="admin-th text-right">Total</th>
                        <th class="admin-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php($payment = $order->payments->first())
                        <tr class="admin-row">
                            <td class="admin-td">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-slate-900 hover:underline">#{{ $order->id }}</a>
                            </td>
                            <td class="admin-td">
                                <span class="block text-slate-900">{{ $order->user?->name ?? $order->contact_name ?? '—' }}</span>
                                <span class="block text-xs text-slate-500">{{ $order->user?->email ?? $order->contact_email }}</span>
                            </td>
                            <td class="admin-td whitespace-nowrap">{{ $order->created_at?->format('M j, Y') }}</td>
                            <td class="admin-td">
                                <x-admin.status-badge :status="$order->status" />
                            </td>
                            <td class="admin-td">
                                @if ($payment)
                                    <x-admin.status-badge :status="$payment->status" />
                                @else
                                    <span class="text-slate-400">No payment</span>
                                @endif
                            </td>
                            <td class="admin-td whitespace-nowrap text-right font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($order->total, $order->currency) }}</td>
                            <td class="admin-td">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $orders->links('components.admin.pagination') }}
    @endif
</x-layouts.admin>
