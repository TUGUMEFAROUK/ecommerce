<x-layouts.admin :title="$customer->name">
    <x-admin.page-header :title="$customer->name">
        <a href="{{ route('admin.customers.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Back to customers
        </a>
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.stat-card label="Email" :value="$customer->email" />
        <x-admin.stat-card label="Joined" :value="$customer->created_at?->format('M j, Y') ?? '—'" />
        <x-admin.stat-card label="Orders" :value="$orderCount" />
        <x-admin.stat-card
            label="Total spent"
            :value="app(\App\Services\SettingsService::class)->get('store_currency').' '.number_format($totalSpent, 2)"
            meta="Recognized sales only"
        />
    </div>

    <section class="rounded border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent orders</h2>
            <a href="{{ route('admin.orders.index', ['search' => $customer->email]) }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                Search all orders
            </a>
        </div>

        @if ($orders->isEmpty())
            <p class="text-sm text-gray-600">This customer has not placed any orders yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 pr-4 font-medium">Order</th>
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 text-right font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="py-2 pr-4">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">#{{ $order->id }}</a>
                                </td>
                                <td class="py-2 pr-4 text-gray-600">{{ $order->created_at?->format('M j, Y') }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $order->status->value }}</td>
                                <td class="py-2 text-right">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
