@php($settings = app(\App\Services\SettingsService::class))

<footer class="mt-24 border-t border-sand bg-white">
    <div class="shell grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-store-brand name-class="font-display text-2xl leading-none tracking-tight text-charcoal" />
            </a>

            @if (filled($settings->get('store_tagline')))
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-charcoal/70">
                    {{ $settings->get('store_tagline') }}
                </p>
            @endif

            <div class="mt-6 space-y-2 text-sm">
                @if (filled($settings->get('contact_email')))
                    <p>
                        <span class="text-charcoal/50">Email</span>
                        <a href="mailto:{{ $settings->get('contact_email') }}" class="link-quiet ml-2">
                            {{ $settings->get('contact_email') }}
                        </a>
                    </p>
                @endif

                @if (filled($settings->get('contact_phone')))
                    <p>
                        <span class="text-charcoal/50">Phone</span>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings->get('contact_phone')) }}" class="link-quiet ml-2">
                            {{ $settings->get('contact_phone') }}
                        </a>
                    </p>
                @endif

                @if (blank($settings->get('contact_email')) && blank($settings->get('contact_phone')))
                    <p class="text-charcoal/60">Contact details are added from the admin panel.</p>
                @endif
            </div>

            @if ($settings->socialLinks() !== [])
                <div class="mt-8">
                    <h3 class="eyebrow">Follow</h3>
                    <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($settings->socialLinks() as $network => $url)
                            <li>
                                <a
                                    href="{{ $url }}"
                                    rel="noopener noreferrer"
                                    target="_blank"
                                    class="link-quiet text-sm"
                                >
                                    {{ $network }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <nav aria-label="Footer categories" class="lg:col-span-3">
            <h3 class="eyebrow">Shop</h3>
            <ul class="mt-4 space-y-2.5 text-sm text-charcoal/75">
                @forelse ($categories as $category)
                    <li>
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="transition-colors duration-200 hover:text-olive">
                            {{ $category->name }}
                        </a>
                    </li>
                @empty
                    <li class="text-charcoal/50">Categories are added from the admin panel.</li>
                @endforelse
                <li>
                    <a href="{{ route('products.index') }}" class="transition-colors duration-200 hover:text-olive">All products</a>
                </li>
                <li>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="transition-colors duration-200 hover:text-olive">New arrivals</a>
                </li>
            </ul>
        </nav>

        <nav aria-label="Customer support" class="lg:col-span-2">
            <h3 class="eyebrow">Support</h3>
            <ul class="mt-4 space-y-2.5 text-sm text-charcoal/75">
                @auth
                    <li><a href="{{ route('account.show') }}" class="transition-colors duration-200 hover:text-olive">Your account</a></li>
                    <li><a href="{{ route('account.show') }}" class="transition-colors duration-200 hover:text-olive">Order history</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="transition-colors duration-200 hover:text-olive">Sign in</a></li>
                    <li><a href="{{ route('register') }}" class="transition-colors duration-200 hover:text-olive">Create an account</a></li>
                @endauth
                <li><a href="{{ route('cart.index') }}" class="transition-colors duration-200 hover:text-olive">Your cart</a></li>
                <li>
                    <a href="{{ route('policies.show', 'shipping') }}" class="transition-colors duration-200 hover:text-olive">Shipping</a>
                </li>
                <li>
                    <a href="{{ route('policies.show', 'returns') }}" class="transition-colors duration-200 hover:text-olive">Returns</a>
                </li>
            </ul>
        </nav>

        <nav aria-label="Policies" class="lg:col-span-3">
            <h3 class="eyebrow">Policies</h3>
            <ul class="mt-4 space-y-2.5 text-sm text-charcoal/75">
                <li>
                    <a href="{{ route('policies.show', 'payments') }}" class="transition-colors duration-200 hover:text-olive">Payments</a>
                </li>
                <li>
                    <a href="{{ route('policies.show', 'privacy') }}" class="transition-colors duration-200 hover:text-olive">Privacy</a>
                </li>
                <li>
                    <a href="{{ route('policies.show', 'terms') }}" class="transition-colors duration-200 hover:text-olive">Terms</a>
                </li>
                <li>
                    <a href="{{ route('policies.show', 'shipping') }}" class="transition-colors duration-200 hover:text-olive">Shipping &amp; delivery</a>
                </li>
                <li>
                    <a href="{{ route('policies.show', 'returns') }}" class="transition-colors duration-200 hover:text-olive">Returns &amp; refunds</a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="border-t border-sand">
        <div class="shell flex flex-col gap-2 py-6 text-xs text-charcoal/55 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ $settings->get('store_name') }}. All rights reserved.</p>
            <p>Demo storefront &mdash; checkout and payment processing are not yet available.</p>
        </div>
    </div>
</footer>
