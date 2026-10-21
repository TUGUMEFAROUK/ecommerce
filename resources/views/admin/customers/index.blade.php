<x-layouts.admin title="Customers">
    <x-admin.page-header title="Customers" />

    <form
        method="GET"
        action="{{ route('admin.customers.index') }}"
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
                placeholder="Name or email"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Search
            </button>
            <a href="{{ route('admin.customers.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    @if ($customers->isEmpty())
        <x-empty-state
            title="No customers found"
            message="Registered customer accounts appear here after they sign up."
        />
    @else
        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 font-medium">Orders</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                                <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->created_at?->format('M j, Y') }}</td>
                            <td class="px-4 py-3">{{ $customer->orders_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm text-gray-700 hover:text-gray-900 hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    @endif
</x-layouts.admin>
