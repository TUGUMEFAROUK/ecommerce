<x-layouts.shop
    :title="$product->name"
    :description="filled($product->description) ? Str::limit(strip_tags($product->description), 155) : null"
>
    @php
        $settings = app(\App\Services\SettingsService::class);
        $inStock = $product->inStock();
        $lowStock = $inStock && $product->stock_quantity <= $settings->lowStockThreshold();
        $category = $product->category;
        $categoryUrl = $category !== null
            ? route('products.index', ['category' => $category->slug])
            : null;
        $flatFee = \App\Support\Money::format($settings->shippingFlatFeeCents() / 100);
        $freeThreshold = \App\Support\Money::format($settings->shippingFreeThresholdCents() / 100);
        $returnsDays = $settings->returnsWindowDays();
    @endphp

    <div class="shell pt-6 lg:pt-10">
        <nav aria-label="Breadcrumb" class="mb-6 lg:mb-10">
            <ol class="flex flex-wrap items-center gap-2 text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal/55">
                <li><a href="{{ route('home') }}" class="transition-colors duration-200 hover:text-olive">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('products.index') }}" class="transition-colors duration-200 hover:text-olive">Products</a></li>
                @if ($category !== null)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ $categoryUrl }}" class="transition-colors duration-200 hover:text-olive">{{ $category->name }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="max-w-[16rem] truncate text-charcoal sm:max-w-none">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-14 xl:gap-20">
            <div class="min-w-0">
                <x-product-gallery :product="$product" :images="$product->images" />
            </div>

            <div class="min-w-0">
                <p class="eyebrow">
                    @if ($category !== null)
                        <a href="{{ $categoryUrl }}" class="transition-colors duration-200 hover:text-charcoal">{{ $category->name }}</a>
                    @else
                        Collection
                    @endif
                </p>

                <h1 class="mt-3 font-display text-4xl leading-[1.05] sm:text-5xl">{{ $product->name }}</h1>

                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[0.6875rem] uppercase tracking-[0.16em]">
                    <span class="text-charcoal/50">SKU {{ $product->sku }}</span>

                    <span aria-hidden="true" class="h-1 w-1 rounded-full bg-sand-deep"></span>

                    <span>
                        @if (! $inStock)
                            <span class="text-charcoal/45">Out of stock</span>
                        @elseif ($lowStock)
                            <span class="text-clay">In stock ({{ $product->stock_quantity }} available) &mdash; only a few left</span>
                        @else
                            <span class="text-olive">In stock ({{ $product->stock_quantity }} available)</span>
                        @endif
                    </span>
                </div>

                <div class="mt-6 flex flex-wrap items-baseline gap-3">
                    <span class="font-display text-3xl text-charcoal">
                        {{ \App\Support\Money::format($product->effectivePrice()) }}
                    </span>

                    @if ($product->isOnSale())
                        <span class="text-lg text-charcoal/45 line-through">
                            {{ \App\Support\Money::format($product->price) }}
                        </span>
                        <span class="rounded-sm bg-clay px-2.5 py-1 text-[0.625rem] font-medium uppercase tracking-[0.15em] text-white">
                            Save {{ $product->discountPercentage() }}%
                        </span>
                    @endif
                </div>

                <p class="mt-2 text-xs text-charcoal/50">
                    Prices and availability are confirmed at checkout.
                </p>

                <x-product-purchase-form :product="$product" class="mt-7" />

                <dl class="mt-8 divide-y divide-sand border-y border-sand">
                    <div class="flex flex-col gap-1 py-4 sm:flex-row sm:gap-6">
                        <dt class="eyebrow shrink-0 pt-0.5 sm:w-24">Delivery</dt>
                        <dd class="text-sm leading-relaxed text-charcoal/70">
                            {{ $settings->get('delivery_estimate') }}.
                            {{ $settings->shippingFlatFeeCents() > 0
                                ? "Standard delivery is {$flatFee}, and free on orders over {$freeThreshold}."
                                : 'Standard delivery is complimentary.' }}
                            <a href="{{ route('policies.show', 'shipping') }}" class="link-quiet">Shipping policy</a>
                        </dd>
                    </div>

                    <div class="flex flex-col gap-1 py-4 sm:flex-row sm:gap-6">
                        <dt class="eyebrow shrink-0 pt-0.5 sm:w-24">Payment</dt>
                        <dd class="text-sm leading-relaxed text-charcoal/70">
                            Secure checkout. Card details are never stored by the store.
                            <a href="{{ route('policies.show', 'payments') }}" class="link-quiet">Payment policy</a>
                        </dd>
                    </div>

                    <div class="flex flex-col gap-1 py-4 sm:flex-row sm:gap-6">
                        <dt class="eyebrow shrink-0 pt-0.5 sm:w-24">Returns</dt>
                        <dd class="text-sm leading-relaxed text-charcoal/70">
                            {{ $returnsDays }}-day returns on unused items in their original packaging.
                            <a href="{{ route('policies.show', 'returns') }}" class="link-quiet">Returns policy</a>
                        </dd>
                    </div>
                </dl>

                <div class="mt-8">
                    <details class="disclosure" open>
                        <summary>
                            <span>Description</span>
                            <span class="disclosure-icon" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                                </svg>
                            </span>
                        </summary>

                        <div class="pb-6 text-[0.9375rem] leading-relaxed text-charcoal/75">
                            @if (filled($product->description))
                                {!! nl2br(e($product->description)) !!}
                            @else
                                <p class="italic text-charcoal/50">No description has been written for this product yet.</p>
                            @endif
                        </div>
                    </details>

                    <details class="disclosure">
                        <summary>
                            <span>Specifications</span>
                            <span class="disclosure-icon" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                                </svg>
                            </span>
                        </summary>

                        <dl class="grid gap-x-8 gap-y-4 pb-6 text-sm sm:grid-cols-2">
                            <div class="flex items-baseline justify-between gap-4 border-b border-sand/70 pb-2">
                                <dt class="text-charcoal/50">SKU</dt>
                                <dd class="text-right text-charcoal">{{ $product->sku }}</dd>
                            </div>

                            <div class="flex items-baseline justify-between gap-4 border-b border-sand/70 pb-2">
                                <dt class="text-charcoal/50">Category</dt>
                                <dd class="text-right text-charcoal">
                                    @if ($category !== null)
                                        <a href="{{ $categoryUrl }}" class="transition-colors duration-200 hover:text-olive">{{ $category->name }}</a>
                                    @else
                                        &mdash;
                                    @endif
                                </dd>
                            </div>

                            <div class="flex items-baseline justify-between gap-4 border-b border-sand/70 pb-2">
                                <dt class="text-charcoal/50">Availability</dt>
                                <dd class="text-right text-charcoal">{{ $inStock ? 'In stock' : 'Out of stock' }}</dd>
                            </div>

                            <div class="flex items-baseline justify-between gap-4 border-b border-sand/70 pb-2">
                                <dt class="text-charcoal/50">Price</dt>
                                <dd class="text-right text-charcoal">{{ \App\Support\Money::format($product->effectivePrice()) }}</dd>
                            </div>
                        </dl>
                    </details>
                </div>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section aria-labelledby="related-heading" class="mt-20 border-t border-sand pt-12 lg:mt-28">
                <x-section-heading
                    id="related-heading"
                    eyebrow="Continue browsing"
                    heading="You may also like"
                    :link="$categoryUrl"
                    :linkLabel="$category !== null ? 'View all '.$category->name : null"
                />

                <div class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($recentlyViewed->isNotEmpty())
            <section aria-labelledby="recently-viewed-heading" class="mt-20 border-t border-sand pt-12 lg:mt-28">
                <x-section-heading
                    id="recently-viewed-heading"
                    eyebrow="Your history"
                    heading="Recently viewed"
                />

                <div class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
                    @foreach ($recentlyViewed as $viewedProduct)
                        <x-product-card :product="$viewedProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.shop>
