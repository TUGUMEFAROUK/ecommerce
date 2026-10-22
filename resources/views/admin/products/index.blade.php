<x-layouts.admin title="Products">
    <x-admin.page-header title="Products" description="Every product in the catalogue, with stock and status at a glance.">
        <x-admin.button :href="route('admin.products.create')">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 5.25v13.5M5.25 12h13.5" stroke-linecap="round" />
            </svg>
            New product
        </x-admin.button>
    </x-admin.page-header>

    <form
        method="GET"
        action="{{ route('admin.products.index') }}"
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
                placeholder="Name or SKU"
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

        <div>
            <label for="filter-category" class="admin-label">Category</label>
            <select id="filter-category" name="category" class="admin-field">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <x-admin.button type="submit">Filter</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.products.index')">Reset</x-admin.button>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-admin.empty-state
            title="No products found"
            message="Nothing matches the current filters. Clear them or create a new product."
        >
            <x-admin.button :href="route('admin.products.create')">New product</x-admin.button>
        </x-admin.empty-state>
    @else
        <div class="admin-table-wrap">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="admin-th">Product</th>
                        <th class="admin-th">Category</th>
                        <th class="admin-th">Price</th>
                        <th class="admin-th">Stock</th>
                        <th class="admin-th">Status</th>
                        <th class="admin-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @php($primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first())
                        <tr class="admin-row">
                            <td class="admin-td">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if ($primaryImage)
                                            <img
                                                src="{{ Storage::disk('public')->url($primaryImage->path) }}"
                                                alt=""
                                                width="44"
                                                height="44"
                                                loading="lazy"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9L12 21.75M3 7.5l9 5.25M3 7.5v9L12 21.75m0-9.5v9.5" stroke-linejoin="round" />
                                            </svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.show', $product) }}" class="block truncate font-medium text-slate-900 hover:underline">
                                            {{ $product->name }}
                                        </a>
                                        <p class="truncate text-xs text-slate-500">{{ $product->sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="admin-td">{{ $product->category?->name ?? '—' }}</td>
                            <td class="admin-td whitespace-nowrap">
                                <span class="font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($product->price) }}</span>
                                @if ($product->sale_price !== null)
                                    <span class="block text-xs text-slate-500 line-through">{{ \App\Support\Money::formatWithCode($product->sale_price) }}</span>
                                @endif
                            </td>
                            <td class="admin-td">
                                @if ($product->stock_quantity === 0)
                                    <x-admin.badge tone="rose">Out of stock</x-admin.badge>
                                @else
                                    <span class="font-medium text-slate-900">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td class="admin-td">
                                <x-admin.status-badge :status="$product->status" />
                            </td>
                            <td class="admin-td">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit</a>

                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.confirm-button
                                            variant="danger"
                                            message="Delete {{ $product->name }}? This removes the product and its stored images."
                                        >
                                            Delete
                                        </x-admin.confirm-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $products->links('components.admin.pagination') }}
    @endif
</x-layouts.admin>
