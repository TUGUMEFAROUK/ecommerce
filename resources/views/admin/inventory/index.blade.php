<x-layouts.admin title="Inventory">
    <x-admin.page-header title="Inventory">
        <a href="{{ route('admin.settings.edit') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Threshold settings
        </a>
    </x-admin.page-header>

    <p class="mb-6 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Showing active products with {{ $threshold }} {{ Str::plural('unit', $threshold) }} or less in stock
        ({{ $products->total() }} {{ Str::plural('product', $products->total()) }}).
        Adjustments below are recorded with an audit trail.
    </p>

    @if ($products->isEmpty())
        <x-empty-state
            title="Stock levels look healthy"
            message="No active product is at or below the low-stock threshold. Adjust the threshold in settings to widen or narrow this list."
        />
    @else
        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">SKU</th>
                        <th class="px-4 py-3 font-medium">In stock</th>
                        <th class="px-4 py-3 font-medium" colspan="3">Adjustment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="font-medium text-gray-900 hover:underline">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $product->sku }}</td>
                            <td class="px-4 py-3 font-semibold {{ $product->stock_quantity === 0 ? 'text-red-600' : 'text-amber-700' }}">
                                {{ $product->stock_quantity }}
                            </td>

                            <td class="px-4 py-3" colspan="3">
                                <form
                                    method="POST"
                                    action="{{ route('admin.inventory.update', $product) }}"
                                    class="flex flex-wrap items-center gap-3"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label for="stock-{{ $product->id }}" class="sr-only">New stock level for {{ $product->name }}</label>
                                        <input
                                            id="stock-{{ $product->id }}"
                                            type="number"
                                            name="stock_quantity"
                                            value="{{ $product->stock_quantity }}"
                                            required
                                            min="0"
                                            max="1000000"
                                            step="1"
                                            class="w-28 rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                        >
                                    </div>

                                    <div class="min-w-40 flex-1">
                                        <label for="reason-{{ $product->id }}" class="sr-only">Reason for {{ $product->name }} adjustment</label>
                                        <input
                                            id="reason-{{ $product->id }}"
                                            type="text"
                                            name="reason"
                                            value=""
                                            maxlength="255"
                                            placeholder="Reason (optional)"
                                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                                        >
                                    </div>

                                    <button type="submit" class="rounded bg-gray-900 px-3 py-2 text-xs text-white hover:bg-gray-700">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif

    <section class="mt-8 rounded border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold">Recent stock movements</h2>

        @if ($movements->isEmpty())
            <p class="text-sm text-gray-600">No stock movements have been recorded yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Product</th>
                            <th class="py-2 pr-4 text-right font-medium">Change</th>
                            <th class="py-2 pr-4 text-right font-medium">After</th>
                            <th class="py-2 pr-4 font-medium">By</th>
                            <th class="py-2 font-medium">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $movement)
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="py-2 pr-4 text-gray-600">{{ $movement->created_at?->format('M j, Y H:i') }}</td>
                                <td class="py-2 pr-4">{{ $movement->product?->name ?? '—' }}</td>
                                <td class="py-2 pr-4 text-right font-medium {{ $movement->change < 0 ? 'text-red-600' : 'text-green-700' }}">
                                    {{ $movement->change > 0 ? '+' : '' }}{{ $movement->change }}
                                </td>
                                <td class="py-2 pr-4 text-right">{{ $movement->stock_after }}</td>
                                <td class="py-2 pr-4 text-gray-600">{{ $movement->user?->name ?? '—' }}</td>
                                <td class="py-2 text-gray-600">{{ $movement->reason ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
