@props(['product'])

<article {{ $attributes->merge(['class' => 'flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:shadow-md']) }}>
    <a href="{{ route('products.show', $product) }}" class="block" tabindex="-1" aria-hidden="true">
        <x-product-image :product="$product" class="aspect-square" />
    </a>

    <div class="flex flex-1 flex-col p-4">
        @if ($product->category !== null)
            <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
        @endif

        <h3 class="mt-1 text-sm font-medium text-gray-900">
            <a href="{{ route('products.show', $product) }}" class="hover:underline">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto flex items-center justify-between pt-3">
            <p class="text-lg font-semibold text-gray-900">
                ${{ number_format((float) $product->price, 2) }}
            </p>

            @if ($product->inStock())
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                    In stock
                </span>
            @else
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                    Out of stock
                </span>
            @endif
        </div>
    </div>
</article>
