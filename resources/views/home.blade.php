@php
    use App\Services\SettingsService;

    $settings = app(SettingsService::class);

    $heroHeading = $settings->get('hero_heading') ?: 'Everything you need, delivered to your door';
    $heroBody = $settings->get('hero_body');
    $heroCtaLabel = $settings->get('hero_cta_label') ?: 'Browse all products';
    $heroCtaUrl = trim($settings->get('hero_cta_url')) ?: route('products.index');

    $promoHeading = $settings->get('promo_heading');
    $promoBody = $settings->get('promo_body');
    $promoCtaLabel = $settings->get('promo_cta_label');
    $promoCtaUrl = trim($settings->get('promo_cta_url')) ?: route('products.index');
@endphp

<x-layouts.shop
    :title="null"
    :description="$settings->get('store_tagline')"
>
    <section class="shell pt-10 lg:pt-16" aria-labelledby="hero-heading">
        <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5">
                @if (filled($settings->get('hero_eyebrow')))
                    <p class="eyebrow">{{ $settings->get('hero_eyebrow') }}</p>
                @endif

                <h1 id="hero-heading" class="mt-5 font-display text-[2.75rem] leading-[1.05] sm:text-6xl lg:text-[4.25rem]">
                    {{ $heroHeading }}
                </h1>

                @if (filled($heroBody))
                    <p class="mt-6 max-w-lg text-base leading-relaxed text-charcoal/70 sm:text-lg">
                        {{ $heroBody }}
                    </p>
                @endif

                <div class="mt-9 flex flex-wrap items-center gap-4">
                    <a href="{{ $heroCtaUrl }}" class="btn btn-primary">
                        {{ $heroCtaLabel }}
                    </a>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="btn btn-ghost">
                        New arrivals
                    </a>
                </div>

                <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-sand pt-8">
                    @foreach ($categories->take(3) as $category)
                        <div>
                            <dt class="text-[0.625rem] uppercase tracking-[0.18em] text-charcoal/50">
                                {{ $category->name }}
                            </dt>
                            <dd class="mt-1.5 font-display text-2xl text-charcoal">
                                {{ $category->products_count }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-sm border border-sand bg-mist">
                    <img
                        src="{{ $heroImage ?? asset('images/editorial-placeholder.svg') }}"
                        alt="{{ $settings->get('store_name') }} — a considered selection of everyday pieces"
                        class="aspect-[4/3] w-full object-cover lg:aspect-[16/11]"
                        width="1200"
                        height="825"
                        fetchpriority="high"
                        decoding="async"
                    >
                </div>
            </div>
        </div>
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="categories-heading">
        <x-section-heading
            eyebrow="Shop by category"
            heading="Curated for every space"
            id="categories-heading"
            :link="route('products.index')"
            link-label="View all categories"
        />

        @if ($categories->isEmpty())
            <x-empty-state
                title="No categories yet"
                message="Categories will appear here once they are added from the admin panel."
            >
                <a href="{{ route('products.index') }}" class="btn btn-outline">Browse the catalogue</a>
            </x-empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('products.index', ['category' => $category->slug]) }}"
                        class="group relative block overflow-hidden rounded-sm border border-sand bg-mist"
                    >
                        <div class="aspect-[4/3] overflow-hidden">
                            <x-category-image
                                :category="$category"
                                class="h-full w-full transition-transform duration-700 ease-out group-hover:scale-[1.04]"
                            />
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-charcoal/75 via-charcoal/25 to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 p-6 text-white">
                            <div>
                                <p class="text-[0.625rem] uppercase tracking-[0.18em] text-white/75">
                                    {{ $category->products_count }} {{ Str::plural('piece', $category->products_count) }}
                                </p>
                                <h3 class="mt-1.5 font-display text-2xl text-white">{{ $category->name }}</h3>
                            </div>

                            <span
                                class="shrink-0 border-b border-white/40 pb-px text-[0.625rem] uppercase tracking-[0.18em] text-white/85 transition-colors duration-200 group-hover:border-white"
                                aria-hidden="true"
                            >
                                Shop
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="featured-heading">
        <x-section-heading
            eyebrow="Our favourites"
            heading="Featured products"
            id="featured-heading"
            :link="route('products.index')"
            link-label="View all"
        />

        @if ($featuredProducts->isEmpty())
            <x-empty-state
                title="No featured products yet"
                message="We are still picking our favourites. Browse the full catalogue to see everything in stock."
            >
                <a href="{{ route('products.index') }}" class="btn btn-outline">Browse products</a>
            </x-empty-state>
        @else
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="new-arrivals-heading">
        <x-section-heading
            eyebrow="Just in"
            heading="New arrivals"
            id="new-arrivals-heading"
            :link="route('products.index', ['sort' => 'newest'])"
            link-label="View all"
        />

        @if ($newArrivals->isEmpty())
            <x-empty-state
                title="No products in the catalog"
                message="New arrivals will appear here as soon as products are added to the store."
            />
        @else
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="bestsellers-heading">
        <x-section-heading
            eyebrow="Most loved"
            heading="Bestsellers"
            id="bestsellers-heading"
            :link="route('products.index')"
            link-label="View all"
        />

        @if ($bestSellers->isEmpty())
            <x-empty-state
                title="No bestsellers yet"
                message="As orders come in, our most popular pieces will rise to the top here."
            />
        @else
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($bestSellers as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="promo-heading">
        <div class="grid overflow-hidden rounded-sm border border-sand bg-white lg:grid-cols-2">
            <div class="order-2 flex flex-col justify-center px-7 py-12 sm:px-12 lg:order-1 lg:px-16 lg:py-20">
                @if (filled($settings->get('promo_eyebrow')))
                    <p class="eyebrow">{{ $settings->get('promo_eyebrow') }}</p>
                @endif

                <h2 id="promo-heading" class="mt-4 font-display text-3xl sm:text-4xl">
                    {{ $promoHeading }}
                </h2>

                @if (filled($promoBody))
                    <p class="mt-5 max-w-lg text-base leading-relaxed text-charcoal/70">
                        {{ $promoBody }}
                    </p>
                @endif

                @if (filled($promoCtaLabel))
                    <div class="mt-9">
                        <a href="{{ $promoCtaUrl }}" class="btn btn-outline">
                            {{ $promoCtaLabel }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="order-1 bg-mist lg:order-2">
                <img
                    src="{{ $promoImage ?? asset('images/editorial-placeholder.svg') }}"
                    alt="{{ $promoHeading }}"
                    class="aspect-[4/3] h-full w-full object-cover lg:aspect-auto lg:min-h-[26rem]"
                    width="1200"
                    height="900"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        </div>
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="benefits-heading">
        <div class="mb-10 max-w-2xl">
            <p class="eyebrow">The {{ $settings->get('store_name') }} promise</p>
            <h2 id="benefits-heading" class="mt-2 font-display text-3xl sm:text-4xl">
                Thoughtful from start to finish
            </h2>
        </div>

        <x-store-benefits />
    </section>

    <section class="shell pt-20 lg:pt-28" aria-labelledby="newsletter-heading">
        <div class="grid overflow-hidden rounded-sm border border-sand bg-white lg:grid-cols-2">
            <div class="order-2 flex flex-col justify-center px-7 py-12 sm:px-12 lg:order-1 lg:px-16 lg:py-20">
                <p class="eyebrow">Stay in the loop</p>

                <x-newsletter-form
                    class="mt-4"
                    :heading="$settings->get('newsletter_heading') ?: 'Join the journal'"
                    :body="$settings->get('newsletter_body')"
                />
            </div>

            <div class="order-1 bg-mist lg:order-2">
                <img
                    src="{{ $newsletterImage ?? asset('images/editorial-placeholder.svg') }}"
                    alt="A quiet corner of the {{ $settings->get('store_name') }} collection"
                    class="aspect-[4/3] h-full w-full object-cover lg:aspect-auto lg:min-h-[26rem]"
                    width="1200"
                    height="900"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        </div>
    </section>
</x-layouts.shop>
