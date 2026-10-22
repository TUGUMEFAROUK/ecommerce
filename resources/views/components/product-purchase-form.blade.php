@props(['product'])

@php
    use App\Services\CartService;

    $inStock = $product->inStock();
    $maxQuantity = $inStock ? min($product->stock_quantity, CartService::MAX_PER_ITEM) : 1;
    $limitPerOrder = CartService::MAX_PER_ITEM;
@endphp

<form
    method="POST"
    action="{{ route('products.cart.add', $product) }}"
    {{ $attributes->merge(['class' => 'w-full']) }}
    data-add-to-cart
>
    @csrf

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="sm:w-36">
            <label for="quantity" class="field-label">Quantity</label>

            <div class="qty-stepper flex h-11 items-stretch rounded-sm border border-sand bg-white transition-colors duration-200 focus-within:border-olive">
                <button
                    type="button"
                    data-qty-step="-1"
                    aria-controls="quantity"
                    aria-label="Decrease quantity"
                    class="flex w-10 shrink-0 items-center justify-center text-lg leading-none text-charcoal/60 transition-colors duration-200 hover:text-charcoal disabled:cursor-not-allowed disabled:opacity-40"
                    @disabled(! $inStock)
                >&minus;</button>

                <input
                    id="quantity"
                    type="number"
                    name="quantity"
                    value="{{ old('quantity', 1) }}"
                    min="1"
                    max="{{ $maxQuantity }}"
                    step="1"
                    inputmode="numeric"
                    autocomplete="off"
                    required
                    class="h-full w-full min-w-0 border-0 bg-transparent px-1 text-center text-sm text-charcoal"
                >

                <button
                    type="button"
                    data-qty-step="1"
                    aria-controls="quantity"
                    aria-label="Increase quantity"
                    class="flex w-10 shrink-0 items-center justify-center text-lg leading-none text-charcoal/60 transition-colors duration-200 hover:text-charcoal disabled:cursor-not-allowed disabled:opacity-40"
                    @disabled(! $inStock)
                >+</button>
            </div>

            @error('quantity')
                <p class="mt-1.5 text-xs text-clay" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0 flex-1">
            @if ($inStock)
                <button type="submit" data-add-to-cart-button class="btn btn-primary w-full">
                    <svg data-add-to-cart-spinner class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" stroke-opacity="0.25" />
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                    <span data-add-to-cart-label>Add to cart</span>
                </button>
            @else
                <button type="submit" disabled aria-disabled="true" class="btn btn-primary w-full cursor-not-allowed opacity-50">
                    Out of stock
                </button>
            @endif
        </div>
    </div>

    <p
        data-add-to-cart-status
        role="status"
        aria-live="polite"
        class="mt-3 flex min-h-5 flex-wrap items-center gap-x-2 gap-y-1 text-sm text-charcoal/60 data-[tone=success]:text-olive data-[tone=error]:text-clay"
    ></p>

    @if ($inStock)
        <p class="mt-1 text-xs text-charcoal/50">
            @if ($product->stock_quantity > $limitPerOrder)
                Maximum {{ $limitPerOrder }} per order.
            @else
                Only {{ $product->stock_quantity }} available.
            @endif
        </p>
    @endif
</form>
