<x-layouts.shop title="Cart">
    <div class="shell pt-10 lg:pt-14">
        <p class="eyebrow">Basket</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl">Your cart</h1>

        @if (session('status'))
            <div class="mt-6 rounded-sm border border-olive/30 bg-olive/10 px-4 py-3 text-sm text-charcoal" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('cart'))
            <div class="mt-6 rounded-sm border border-clay/30 bg-clay/10 px-4 py-3 text-sm text-charcoal" role="alert">
                {{ $errors->first('cart') }}
            </div>
        @endif

        @if ($lines->isEmpty())
            <div class="mt-10">
                <x-empty-state title="Your cart is empty" message="Browse the catalog and add products to your cart.">
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Browse products</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-10 flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-16">
                <div class="min-w-0 flex-1">
                    <ul class="flex flex-col gap-6">
                        @foreach ($lines as $line)
                            @php($product = $line['product'])

                            <li class="flex flex-col gap-4 border-b border-sand pb-6 sm:flex-row sm:items-center">
                                <div class="h-28 w-24 shrink-0 overflow-hidden rounded-sm border border-sand bg-white">
                                    <x-product-image :product="$product" class="h-full w-full" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    @if ($product->category !== null)
                                        <p class="text-[0.625rem] uppercase tracking-[0.18em] text-charcoal/50">{{ $product->category->name }}</p>
                                    @endif

                                    <h2 class="mt-1 font-display text-lg leading-snug">
                                        <a href="{{ route('products.show', $product) }}" class="transition-colors duration-200 hover:text-olive">
                                            {{ $product->name }}
                                        </a>
                                    </h2>

                                    <p class="mt-1 text-[0.6875rem] uppercase tracking-[0.15em] text-charcoal/50">SKU {{ $product->sku }}</p>

                                    <p class="mt-2 text-sm text-charcoal/70">
                                        {{ \App\Support\Money::format($product->effectivePrice()) }} each
                                    </p>

                                    @if ($problems[$product->getKey()] ?? null)
                                        <p class="mt-2 text-xs font-medium text-clay" role="alert">
                                            {{ $problems[$product->getKey()] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-4 sm:flex-col sm:items-end sm:gap-3">
                                    @if ($product->status === \App\Enums\ProductStatus::Active && $product->stock_quantity >= 1)
                                        <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')

                                            <label for="quantity-{{ $product->getKey() }}" class="sr-only">
                                                Quantity for {{ $product->name }}
                                            </label>
                                            <input
                                                id="quantity-{{ $product->getKey() }}"
                                                type="number"
                                                name="quantity"
                                                value="{{ $line['quantity'] }}"
                                                min="1"
                                                max="{{ min($product->stock_quantity, 10) }}"
                                                required
                                                class="h-10 w-20 rounded-sm border border-sand bg-white px-2.5 text-sm focus:border-olive focus:outline-none"
                                            >
                                            <button type="submit" class="btn btn-outline btn-sm">Update</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('cart.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-[0.6875rem] uppercase tracking-[0.16em] text-charcoal/50 transition-colors duration-200 hover:text-clay"
                                            aria-label="Remove {{ $product->name }} from cart"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </div>

                                <p class="font-display text-xl sm:w-28 sm:text-right">
                                    {{ \App\Support\Money::format($lineTotals[$product->getKey()]) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                        <a href="{{ route('products.index') }}" class="link-quiet text-sm">
                            &larr; Continue shopping
                        </a>

                        <form method="POST" action="{{ route('cart.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[0.6875rem] uppercase tracking-[0.16em] text-charcoal/50 transition-colors duration-200 hover:text-clay">
                                Clear cart
                            </button>
                        </form>
                    </div>
                </div>

                <div class="w-full shrink-0 lg:w-96">
                    <x-order-summary :lines="$lines" :quote="$quote" />

                    <a href="{{ route('checkout.show') }}" class="btn btn-primary mt-6 w-full">Proceed to checkout</a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.shop>
