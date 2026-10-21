<header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="flex h-9 w-9 items-center justify-center rounded bg-gray-900 text-sm font-bold text-white" aria-hidden="true">
                {{ strtoupper(substr($settings->get('store_name'), 0, 1)) }}
            </span>
            <span class="text-lg font-semibold text-gray-900">{{ $settings->get('store_name') }}</span>
        </a>

        <form action="{{ route('products.index') }}" method="GET" role="search" class="order-3 w-full max-w-md flex-1 md:order-none md:w-auto">
            <label for="site-search" class="sr-only">Search products</label>
            <div class="flex">
                <input
                    id="site-search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search products&hellip;"
                    maxlength="100"
                    class="w-full rounded-l border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                >
                <button
                    type="submit"
                    class="rounded-r bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                >
                    Search
                </button>
            </div>
        </form>

        <nav aria-label="Account" class="ml-auto flex items-center gap-4 text-sm">
            <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-gray-900">
                Cart
                @if (($cartItemCount ?? 0) > 0)
                    <span class="ml-0.5 rounded-full bg-gray-900 px-1.5 py-0.5 text-xs font-semibold text-white">
                        {{ $cartItemCount }}
                    </span>
                @endif
            </a>

            @auth
                <a href="{{ route('account.show') }}" class="text-gray-700 hover:text-gray-900">Account</a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900">Admin</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-700 hover:text-gray-900">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Log in</a>
                <a href="{{ route('register') }}" class="rounded bg-gray-900 px-4 py-2 text-white hover:bg-gray-700">Register</a>
            @endauth
        </nav>
    </div>

    <nav aria-label="Categories" class="mx-auto max-w-7xl px-4 pb-3">
        <ul class="flex gap-5 overflow-x-auto text-sm">
            <li>
                <a
                    href="{{ route('products.index') }}"
                    class="whitespace-nowrap text-gray-700 hover:text-gray-900{{ request('category') === null && request()->routeIs('products.index') ? ' font-semibold text-gray-900' : '' }}"
                >
                    All products
                </a>
            </li>
            @foreach ($categories as $category)
                <li>
                    <a
                        href="{{ route('products.index', ['category' => $category->slug]) }}"
                        @if (request('category') === $category->slug) aria-current="page" class="whitespace-nowrap font-semibold text-gray-900" @else class="whitespace-nowrap text-gray-700 hover:text-gray-900" @endif
                    >
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>
