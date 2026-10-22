<x-layouts.admin title="Customers">
    <x-admin.page-header title="Customers" description="Registered customer accounts and their order history." />

    <form
        method="GET"
        action="{{ route('admin.customers.index') }}"
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
                placeholder="Name or email"
                class="admin-field"
            >
        </div>

        <div class="flex items-center gap-2">
            <x-admin.button type="submit">Search</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.customers.index')">Reset</x-admin.button>
        </div>
    </form>

    @if ($customers->isEmpty())
        <x-admin.empty-state
            title="No customers found"
            message="Registered customer accounts appear here after they sign up."
        />
    @else
        <div class="admin-table-wrap">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="admin-th">Customer</th>
                        <th class="admin-th">Joined</th>
                        <th class="admin-th">Orders</th>
                        <th class="admin-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr class="admin-row">
                            <td class="admin-td">
                                <span class="block font-medium text-slate-900">{{ $customer->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $customer->email }}</span>
                            </td>
                            <td class="admin-td whitespace-nowrap">{{ $customer->created_at?->format('M j, Y') }}</td>
                            <td class="admin-td">{{ number_format($customer->orders_count) }}</td>
                            <td class="admin-td">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $customers->links('components.admin.pagination') }}
    @endif
</x-layouts.admin>
