<x-layouts.admin title="Products">
    <x-admin.page-header title="Products">
        <a
            href="{{ route('admin.products.create') }}"
            class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
        >
            New product
        </a>
    </x-admin.page-header>

    <form
        method="GET"
        action="{{ route('admin.products.index') }}"
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
                placeholder="Name or SKU"
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

        <div>
            <label for="filter-category" class="mb-1 block text-sm font-medium text-gray-700">Category</label>
            <select
                id="filter-category"
                name="category"
                class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Filter
            </button>
            <a href="{{ route('admin.products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-empty-state
            title="No products found"
            message="Nothing matches the current filters. Clear them or create a new product."
        />
    @else
        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Stock</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.products.show', $product) }}" class="font-medium text-gray-900 hover:underline">
                                    {{ $product->name }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $product->price, 2) }}</td>
                            <td class="px-4 py-3 {{ $product->stock_quantity === 0 ? 'font-semibold text-red-600' : '' }}">
                                {{ $product->stock_quantity }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match ($product->status->value) {
                                        'active' => 'bg-green-100 text-green-800',
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        default => 'bg-amber-100 text-amber-800',
                                    } }}">
                                    {{ Str::headline($product->status->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3 text-sm">
                                    <a href="{{ route('admin.products.show', $product) }}" class="text-gray-700 hover:text-gray-900 hover:underline">View</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-gray-700 hover:text-gray-900 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
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
</x-layouts.admin>
