<x-layouts.shop :title="$currentCategory?->name ?? 'Products'">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-10">
        <aside class="w-full shrink-0 lg:w-64" aria-label="Catalog filters">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-4 rounded border border-gray-200 bg-white p-5">
                <div>
                    <label for="filter-search" class="mb-1 block text-sm font-medium text-gray-900">Search</label>
                    <input
                        id="filter-search"
                        type="search"
                        name="search"
                        value="{{ old('search', request('search')) }}"
                        maxlength="100"
                        placeholder="Name, description, or SKU"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                    >
                    @error('search')
                        <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="filter-category" class="mb-1 block text-sm font-medium text-gray-900">Category</label>
                    <select
                        id="filter-category"
                        name="category"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                    >
                        <option value="">All categories</option>
                        @foreach ($catalogCategories as $category)
                            <option
                                value="{{ $category->slug }}"
                                @selected(old('category', request('category')) === $category->slug)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="filter-sort" class="mb-1 block text-sm font-medium text-gray-900">Sort by</label>
                    <select
                        id="filter-sort"
                        name="sort"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                    >
                        <option value="newest" @selected(in_array(old('sort', request('sort', 'newest')), ['newest', null], true))>Newest first</option>
                        <option value="price_asc" @selected(old('sort', request('sort')) === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected(old('sort', request('sort')) === 'price_desc')>Price: high to low</option>
                    </select>
                    @error('sort')
                        <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="filter-availability" class="mb-1 block text-sm font-medium text-gray-900">Availability</label>
                    <select
                        id="filter-availability"
                        name="availability"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                    >
                        <option value="all" @selected(in_array(old('availability', request('availability', 'all')), ['all', null], true))>Any</option>
                        <option value="in_stock" @selected(old('availability', request('availability')) === 'in_stock')>In stock only</option>
                        <option value="out_of_stock" @selected(old('availability', request('availability')) === 'out_of_stock')>Out of stock only</option>
                    </select>
                    @error('availability')
                        <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="flex-1 rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                        Apply filters
                    </button>
                    <a href="{{ route('products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Reset
                    </a>
                </div>
            </form>
        </aside>

        <div class="min-w-0 flex-1">
            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                <h1 class="text-xl font-semibold">
                    @if ($currentCategory !== null)
                        {{ $currentCategory->name }}
                    @elseif (filled(request('search')))
                        Search results for &ldquo;{{ request('search') }}&rdquo;
                    @else
                        All products
                    @endif
                </h1>

                <p class="text-sm text-gray-600">
                    {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
                </p>
            </div>

            @if ($products->isEmpty())
                <x-empty-state
                    title="No products found"
                    message="We could not find any products matching your search and filters."
                >
                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-900 underline">
                        Clear search and filters
                    </a>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.shop>
