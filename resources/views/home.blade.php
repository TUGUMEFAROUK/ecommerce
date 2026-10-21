<x-layouts.shop>
    <section class="rounded-lg bg-gradient-to-r from-gray-900 to-gray-700 px-6 py-12 text-white sm:px-12 sm:py-16">
        <p class="text-sm font-semibold uppercase tracking-wide text-gray-300">New season, new deals</p>
        <h1 class="mt-2 max-w-2xl text-3xl font-bold sm:text-4xl">Everything you need, delivered to your door</h1>
        <p class="mt-3 max-w-xl text-gray-300">
            Browse electronics, home essentials, books, and more from our catalog of quality products.
        </p>
        <a
            href="{{ route('products.index') }}"
            class="mt-6 inline-block rounded bg-white px-6 py-3 text-sm font-semibold text-gray-900 hover:bg-gray-100"
        >
            Browse all products
        </a>
    </section>

    <section class="mt-12" aria-labelledby="featured-heading">
        <div class="mb-5 flex items-baseline justify-between">
            <h2 id="featured-heading" class="text-xl font-semibold">Featured products</h2>
            <a href="{{ route('products.index') }}" class="text-sm text-gray-700 underline hover:text-gray-900">View all</a>
        </div>

        @if ($featuredProducts->isEmpty())
            <x-empty-state
                title="No featured products yet"
                message="We are still picking our favourites. Browse the full catalog to see everything in stock."
            >
                <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-900 underline">
                    Browse products
                </a>
            </x-empty-state>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-12" aria-labelledby="new-arrivals-heading">
        <div class="mb-5 flex items-baseline justify-between">
            <h2 id="new-arrivals-heading" class="text-xl font-semibold">New arrivals</h2>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-sm text-gray-700 underline hover:text-gray-900">View all</a>
        </div>

        @if ($newArrivals->isEmpty())
            <x-empty-state
                title="No products in the catalog"
                message="New arrivals will appear here as soon as products are added to the store."
            />
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.shop>
