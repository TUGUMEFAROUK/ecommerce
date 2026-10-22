<x-layouts.shop :title="$currentCategory?->name ?? 'Products'">
    <div class="shell pt-10 lg:pt-14">
        <nav aria-label="Breadcrumb" class="mb-8">
            <ol class="flex flex-wrap items-center gap-2 text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal/55">
                <li><a href="{{ route('home') }}" class="transition-colors duration-200 hover:text-olive">Home</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-charcoal">
                    {{ $currentCategory?->name ?? 'Products' }}
                </li>
            </ol>
        </nav>

        <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-14">
            <aside class="w-full shrink-0 lg:w-64" aria-label="Catalog filters">
                <form method="GET" action="{{ route('products.index') }}" class="surface flex flex-col gap-5 p-6">
                    <h2 class="font-display text-xl">Refine</h2>

                    <div>
                        <label for="filter-search" class="field-label">Search</label>
                        <input
                            id="filter-search"
                            type="search"
                            name="search"
                            value="{{ old('search', request('search')) }}"
                            maxlength="100"
                            placeholder="Name, description or SKU"
                            class="field"
                        >
                        @error('search')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="filter-category" class="field-label">Category</label>
                        <select id="filter-category" name="category" class="field">
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
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="filter-sort" class="field-label">Sort by</label>
                        <select id="filter-sort" name="sort" class="field">
                            <option value="newest" @selected(in_array(old('sort', request('sort', 'newest')), ['newest', null], true))>Newest first</option>
                            <option value="price_asc" @selected(old('sort', request('sort')) === 'price_asc')>Price: low to high</option>
                            <option value="price_desc" @selected(old('sort', request('sort')) === 'price_desc')>Price: high to low</option>
                        </select>
                        @error('sort')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="filter-availability" class="field-label">Availability</label>
                        <select id="filter-availability" name="availability" class="field">
                            <option value="all" @selected(in_array(old('availability', request('availability', 'all')), ['all', null], true))>Any</option>
                            <option value="in_stock" @selected(old('availability', request('availability')) === 'in_stock')>In stock only</option>
                            <option value="out_of_stock" @selected(old('availability', request('availability')) === 'out_of_stock')>Out of stock only</option>
                        </select>
                        @error('availability')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 pt-1">
                        <button type="submit" class="btn btn-primary w-full">Apply filters</button>
                        <a href="{{ route('products.index') }}" class="btn btn-ghost w-full">Reset</a>
                    </div>
                </form>
            </aside>

            <div class="min-w-0 flex-1">
                <div class="mb-8 flex flex-wrap items-baseline justify-between gap-3 border-b border-sand pb-6">
                    <h1 class="font-display text-3xl sm:text-4xl">
                        @if ($currentCategory !== null)
                            {{ $currentCategory->name }}
                        @elseif (filled(request('search')))
                            Search results for &ldquo;{{ request('search') }}&rdquo;
                        @else
                            All products
                        @endif
                    </h1>

                    <p class="text-sm text-charcoal/55">
                        {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
                    </p>
                </div>

                @if ($products->isEmpty())
                    <x-empty-state
                        title="No products found"
                        message="We could not find any products matching your search and filters."
                    >
                        <a href="{{ route('products.index') }}" class="btn btn-outline">Clear search and filters</a>
                    </x-empty-state>
                @else
                    <div class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-14">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.shop>
