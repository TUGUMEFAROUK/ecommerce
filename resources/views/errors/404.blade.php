<x-layouts.shop title="Page not found">
    <div class="py-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Error 404</p>

        <h1 class="mt-2 text-2xl font-bold text-gray-900">We could not find that page</h1>

        <p class="mt-3 max-w-lg text-gray-600">
            The product or page you are looking for may have been removed, renamed,
            or it never existed. Try browsing the catalog instead.
        </p>

        <div class="mt-6 flex flex-wrap gap-4">
            <a href="{{ route('products.index') }}" class="rounded bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                Browse products
            </a>
            <a href="{{ route('home') }}" class="rounded border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                Back to home
            </a>
        </div>
    </div>
</x-layouts.shop>
