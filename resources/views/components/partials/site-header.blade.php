@php($settings = app(\App\Services\SettingsService::class))

<header class="sticky top-0 z-40 border-b border-sand bg-white/95 backdrop-blur-sm">
    <div class="shell">
        <div class="flex h-16 items-center justify-between gap-4 lg:h-20 lg:gap-8">
            <button
                type="button"
                data-nav-toggle
                aria-expanded="false"
                aria-controls="mobile-navigation"
                class="-ml-2 inline-flex h-10 w-10 items-center justify-center rounded-sm text-charcoal transition-colors duration-200 hover:bg-ivory lg:hidden"
            >
                <span class="sr-only">Open menu</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" stroke-linecap="round" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-store-brand name-class="font-display text-xl leading-none tracking-tight text-charcoal lg:text-2xl" />
            </a>

            <nav aria-label="Primary" class="hidden items-center gap-7 lg:flex">
                <a
                    href="{{ route('products.index') }}"
                    class="text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal transition-colors duration-200 hover:text-olive {{ request()->routeIs('products.index') && request('category') === null ? 'text-olive' : '' }}"
                >
                    Shop all
                </a>
                <a
                    href="{{ route('products.index', ['sort' => 'newest']) }}"
                    class="text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal transition-colors duration-200 hover:text-olive"
                >
                    New arrivals
                </a>
                <a
                    href="{{ route('products.index', ['availability' => 'in_stock']) }}"
                    class="text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal transition-colors duration-200 hover:text-olive"
                >
                    In stock
                </a>
            </nav>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button
                    type="button"
                    data-search-toggle
                    aria-expanded="false"
                    aria-controls="site-search-panel"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-sm text-charcoal transition-colors duration-200 hover:bg-ivory lg:hidden"
                >
                    <span class="sr-only">Search products</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.25" />
                        <path d="m15.75 15.75 3.5 3.5" stroke-linecap="round" />
                    </svg>
                </button>

                <form action="{{ route('products.index') }}" method="GET" role="search" class="hidden w-56 lg:block xl:w-72">
                    <label for="site-search" class="sr-only">Search products</label>
                    <div class="relative">
                        <input
                            id="site-search"
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search products"
                            maxlength="100"
                            class="field pr-10"
                        >
                        <button
                            type="submit"
                            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-charcoal/60 transition-colors duration-200 hover:text-olive"
                        >
                            <span class="sr-only">Search</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <circle cx="11" cy="11" r="6.25" />
                                <path d="m15.75 15.75 3.5 3.5" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                </form>

                @auth
                    @if (auth()->user()->isAdmin())
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex h-10 items-center gap-2 px-2 text-charcoal transition-colors duration-200 hover:text-olive"
                        >
                            <span class="sr-only">Admin panel</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M4 5.75h16M4 12h16M4 18.25h16" stroke-linecap="round" />
                                <circle cx="8" cy="5.75" r="1.5" />
                                <circle cx="16" cy="12" r="1.5" />
                                <circle cx="10" cy="18.25" r="1.5" />
                            </svg>
                            <span class="hidden text-[0.6875rem] uppercase tracking-[0.18em] xl:inline">Admin</span>
                        </a>
                    @endif

                    <a
                        href="{{ route('account.show') }}"
                        class="inline-flex h-10 items-center gap-2 px-2 text-charcoal transition-colors duration-200 hover:text-olive"
                    >
                        <span class="sr-only">Your account</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <circle cx="12" cy="8.25" r="3.75" />
                            <path d="M4.75 19.75c.9-3.3 3.6-5 7.25-5s6.35 1.7 7.25 5" stroke-linecap="round" />
                        </svg>
                        <span class="hidden text-[0.6875rem] uppercase tracking-[0.18em] xl:inline">Account</span>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex h-10 items-center gap-2 px-2 text-charcoal transition-colors duration-200 hover:text-olive"
                    >
                        <span class="sr-only">Sign in</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <circle cx="12" cy="8.25" r="3.75" />
                            <path d="M4.75 19.75c.9-3.3 3.6-5 7.25-5s6.35 1.7 7.25 5" stroke-linecap="round" />
                        </svg>
                        <span class="hidden text-[0.6875rem] uppercase tracking-[0.18em] xl:inline">Sign in</span>
                    </a>
                @endauth

                <a
                    href="{{ route('cart.index') }}"
                    class="inline-flex h-10 items-center gap-2 px-2 text-charcoal transition-colors duration-200 hover:text-olive"
                >
                    <span class="sr-only">Shopping cart</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M6.5 8h11l-1 11h-9L6.5 8Z" stroke-linejoin="round" />
                        <path d="M9 8V6.75a3 3 0 0 1 6 0V8" stroke-linecap="round" />
                    </svg>
                    <span class="hidden text-[0.6875rem] uppercase tracking-[0.18em] xl:inline">Cart</span>
                    <span
                        data-cart-count
                        class="flex h-5 min-w-5 items-center justify-center rounded-full bg-olive px-1.5 text-[0.625rem] font-medium leading-none {{ ($cartItemCount ?? 0) > 0 ? '' : 'hidden' }} text-white">
                        {{ $cartItemCount ?? 0 }}
                    </span>
                </a>
            </div>
        </div>

        <nav aria-label="Categories" class="hidden border-t border-sand lg:block">
            <ul class="-mx-1 flex items-center gap-7 overflow-x-auto py-3">
                <li>
                    <a
                        href="{{ route('products.index') }}"
                        @if (request()->routeIs('products.index') && request('category') === null)
                            aria-current="page"
                            class="whitespace-nowrap px-1 text-[0.6875rem] uppercase tracking-[0.18em] text-olive"
                        @else
                            class="whitespace-nowrap px-1 text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal transition-colors duration-200 hover:text-olive"
                        @endif
                    >
                        All
                    </a>
                </li>
                @foreach ($categories as $category)
                    <li>
                        <a
                            href="{{ route('products.index', ['category' => $category->slug]) }}"
                            @if (request('category') === $category->slug)
                                aria-current="page"
                                class="whitespace-nowrap px-1 text-[0.6875rem] uppercase tracking-[0.18em] text-olive"
                            @else
                                class="whitespace-nowrap px-1 text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal transition-colors duration-200 hover:text-olive"
                            @endif
                        >
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div id="site-search-panel" data-search-panel hidden class="border-t border-sand bg-white lg:hidden">
        <div class="shell py-4">
            <form action="{{ route('products.index') }}" method="GET" role="search">
                <label for="site-search-mobile" class="sr-only">Search products</label>
                <div class="relative">
                    <input
                        id="site-search-mobile"
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search products"
                        maxlength="100"
                        class="field pr-10"
                    >
                    <button
                        type="submit"
                        class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-charcoal/60 transition-colors duration-200 hover:text-olive"
                    >
                        <span class="sr-only">Search</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.25" />
                            <path d="m15.75 15.75 3.5 3.5" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        id="mobile-navigation"
        data-nav-panel
        hidden
        class="fixed inset-x-0 bottom-0 top-16 z-30 overflow-y-auto border-t border-sand bg-ivory lg:hidden"
    >
        <div class="shell py-6">
            <div class="flex items-center justify-between">
                <p class="eyebrow">Menu</p>
                <button
                    type="button"
                    data-nav-close
                    class="inline-flex h-10 w-10 items-center justify-center rounded-sm text-charcoal transition-colors duration-200 hover:bg-white"
                >
                    <span class="sr-only">Close menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <nav aria-label="Mobile" class="mt-6">
                <ul class="divide-y divide-sand border-y border-sand">
                    <li>
                        <a href="{{ route('products.index') }}" class="block py-4 font-display text-2xl text-charcoal">
                            Shop all
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="block py-4 font-display text-2xl text-charcoal">
                            New arrivals
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', ['availability' => 'in_stock']) }}" class="block py-4 font-display text-2xl text-charcoal">
                            In stock now
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cart.index') }}" class="block py-4 font-display text-2xl text-charcoal">
                            Cart
                            @if (($cartItemCount ?? 0) > 0)
                                <span class="ml-1 align-middle text-sm text-olive">({{ $cartItemCount }})</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </nav>

            <nav aria-label="Mobile categories" class="mt-8">
                <p class="eyebrow">Categories</p>
                <ul class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3">
                    @foreach ($categories as $category)
                        <li>
                            <a
                                href="{{ route('products.index', ['category' => $category->slug]) }}"
                                class="text-sm text-charcoal/80 transition-colors duration-200 hover:text-olive"
                            >
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="mt-8 flex flex-col gap-3">
                @auth
                    <a href="{{ route('account.show') }}" class="btn btn-outline w-full">Your account</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary w-full">Admin panel</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost w-full">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline w-full">Sign in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary w-full">Create an account</a>
                @endauth
            </div>
        </div>
    </div>
</header>
