<x-layouts.shop title="Page not found">
    <div class="shell flex min-h-[60vh] flex-col items-center justify-center py-16 text-center">
        <p class="eyebrow">Error 404</p>

        <h1 class="mt-4 max-w-xl font-display text-4xl sm:text-5xl">We could not find that page</h1>

        <p class="mt-4 max-w-lg text-charcoal/65">
            The product or page you are looking for may have been removed, renamed,
            or it never existed. Try browsing the catalog instead.
        </p>

        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('products.index') }}" class="btn btn-primary">Browse products</a>
            <a href="{{ route('home') }}" class="btn btn-outline">Back to home</a>
        </div>
    </div>
</x-layouts.shop>
