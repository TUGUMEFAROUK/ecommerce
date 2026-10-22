@props(['product'])

@php
    use App\Services\SettingsService;
    use App\Support\Money;

    $primaryImage = $product->images->first();
    $secondaryImage = $product->images->skip(1)->first();
    $onSale = $product->isOnSale();
    $lowStock = $product->inStock()
        && $product->stock_quantity <= app(SettingsService::class)->lowStockThreshold();
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col']) }} data-product-card>
    <div class="relative overflow-hidden rounded-sm border border-sand bg-white">
        <a
            href="{{ route('products.show', $product) }}"
            class="block"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="relative aspect-[4/5] overflow-hidden">
                <x-product-image :product="$product" :image="$primaryImage" />

                @if ($secondaryImage !== null)
                    <x-product-image
                        :product="$product"
                        :image="$secondaryImage"
                        :alt="$product->name.' — alternate view'"
                        class="absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                    />
                @endif
            </div>
        </a>

        @if ($onSale)
            <span class="absolute left-3 top-3 rounded-sm bg-clay px-2.5 py-1 text-[0.625rem] font-medium uppercase tracking-[0.15em] text-white">
                &minus;{{ $product->discountPercentage() }}%
            </span>
        @elseif ($product->featured)
            <span class="absolute left-3 top-3 rounded-sm bg-white/90 px-2.5 py-1 text-[0.625rem] font-medium uppercase tracking-[0.15em] text-charcoal">
                Featured
            </span>
        @endif

        @if ($product->inStock())
            <form
                method="POST"
                action="{{ route('products.cart.add', $product) }}"
                class="absolute inset-x-3 bottom-3 opacity-100 transition-opacity duration-300 lg:opacity-0 lg:group-hover:opacity-100 lg:focus-within:opacity-100"
            >
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-sm w-full border-charcoal bg-white/95 text-charcoal backdrop-blur-sm hover:border-charcoal hover:bg-charcoal hover:text-white">
                    Add to cart
                </button>
            </form>
        @endif
    </div>

    <div class="flex flex-1 flex-col pt-4">
        @if ($product->category !== null)
            <p class="text-[0.625rem] uppercase tracking-[0.18em] text-charcoal/50">{{ $product->category->name }}</p>
        @endif

        <h3 class="mt-1.5 font-display text-lg leading-snug text-charcoal">
            <a href="{{ route('products.show', $product) }}" class="transition-colors duration-200 hover:text-olive">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-2 flex flex-wrap items-baseline gap-x-2.5 gap-y-1">
            <span class="text-base text-charcoal {{ $onSale ? 'text-clay' : '' }}">
                {{ Money::format($product->effectivePrice()) }}
            </span>

            @if ($onSale)
                <span class="text-sm text-charcoal/45 line-through">
                    {{ Money::format($product->price) }}
                </span>
            @endif
        </div>

        <p class="mt-2 text-[0.6875rem] uppercase tracking-[0.15em]">
            @if (! $product->inStock())
                <span class="text-charcoal/45">Out of stock</span>
            @elseif ($lowStock)
                <span class="text-clay">Only {{ $product->stock_quantity }} left</span>
            @else
                <span class="text-olive">In stock</span>
            @endif
        </p>
    </div>
</article>
