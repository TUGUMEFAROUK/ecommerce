<x-layouts.admin title="Inventory">
    <x-admin.page-header title="Inventory" description="Adjust stock levels and review the audit trail.">
        <x-admin.button variant="secondary" :href="route('admin.settings.edit')">Threshold settings</x-admin.button>
    </x-admin.page-header>

    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
        <p>
            Showing active products with {{ $threshold }} {{ Str::plural('unit', $threshold) }} or less in stock
            ({{ $products->total() }} {{ Str::plural('product', $products->total()) }}).
            Adjustments below are recorded with an audit trail.
        </p>
    </div>

    @if ($products->isEmpty())
        <x-admin.empty-state
            title="Stock levels look healthy"
            message="No active product is at or below the low-stock threshold. Adjust the threshold in settings to widen or narrow this list."
        />
    @else
        <div class="admin-table-wrap">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="admin-th">Product</th>
                        <th class="admin-th">SKU</th>
                        <th class="admin-th">In stock</th>
                        <th class="admin-th">Adjustment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="admin-row">
                            <td class="admin-td">
                                <a href="{{ route('admin.products.edit', $product) }}" class="font-medium text-slate-900 hover:underline">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="admin-td">{{ $product->sku }}</td>
                            <td class="admin-td">
                                @if ($product->stock_quantity === 0)
                                    <x-admin.badge tone="rose">{{ $product->stock_quantity }}</x-admin.badge>
                                @else
                                    <x-admin.badge tone="amber">{{ $product->stock_quantity }}</x-admin.badge>
                                @endif
                            </td>
                            <td class="admin-td">
                                <form
                                    method="POST"
                                    action="{{ route('admin.inventory.update', $product) }}"
                                    class="flex flex-wrap items-center gap-2"
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
                                            class="admin-field w-28"
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
                                            class="admin-field"
                                        >
                                    </div>

                                    <x-admin.button type="submit" size="sm">Save</x-admin.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $products->links('components.admin.pagination') }}
    @endif

    <section class="admin-card mt-6">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">Recent stock movements</h2>
            <p class="text-sm text-slate-500">Every quantity change, with who made it and why.</p>
        </div>

        @if ($movements->isEmpty())
            <x-admin.empty-state
                title="No stock movements yet"
                message="Adjustments are recorded here as soon as a quantity changes."
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="admin-th">Date</th>
                            <th class="admin-th">Product</th>
                            <th class="admin-th text-right">Change</th>
                            <th class="admin-th text-right">After</th>
                            <th class="admin-th">By</th>
                            <th class="admin-th">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $movement)
                            <tr class="admin-row">
                                <td class="admin-td whitespace-nowrap">{{ $movement->created_at?->format('M j, Y H:i') }}</td>
                                <td class="admin-td">{{ $movement->product?->name ?? '—' }}</td>
                                <td class="admin-td text-right font-medium {{ $movement->change < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $movement->change > 0 ? '+' : '' }}{{ $movement->change }}
                                </td>
                                <td class="admin-td text-right">{{ $movement->stock_after }}</td>
                                <td class="admin-td">{{ $movement->user?->name ?? '—' }}</td>
                                <td class="admin-td">{{ $movement->reason ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.admin>
