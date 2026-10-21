<x-layouts.admin title="Orders">
    <x-admin.page-header title="Orders" />

    <form
        method="GET"
        action="{{ route('admin.orders.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3 rounded border border-gray-200 bg-white p-4"
    >
        <div class="min-w-48 flex-1">
            <label for="filter-search" class="mb-1 block text-sm font-medium text-gray-700">Search</label>
            <input
                id="filter-search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                maxlength="100"
                placeholder="Order number, customer name or email"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
        </div>

        <div>
            <label for="filter-status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select
                id="filter-status"
                name="status"
                class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ Str::headline($status->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Filter
            </button>
            <a href="{{ route('admin.orders.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    @if ($orders->isEmpty())
        <x-empty-state
            title="No orders found"
            message="Orders appear here as soon as customers complete checkout."
        />
    @else
        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Total</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $order->id }}</td>
                            <td class="px-4 py-3">
                                {{ $order->user?->name ?? $order->contact_name ?? '—' }}
                                <p class="text-xs text-gray-500">{{ $order->user?->email ?? $order->contact_email }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->created_at?->format('M j, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match ($order->status->value) {
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled', 'refunded' => 'bg-red-100 text-red-800',
                                        'pending' => 'bg-gray-100 text-gray-700',
                                        default => 'bg-blue-100 text-blue-800',
                                    } }}">
                                    {{ Str::headline($order->status->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-gray-700 hover:text-gray-900 hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</x-layouts.admin>
