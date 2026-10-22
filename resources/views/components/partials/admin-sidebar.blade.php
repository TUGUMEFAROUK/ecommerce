<nav aria-label="Admin" class="flex flex-1 flex-col gap-1">
    <p class="px-2 pb-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Navigation</p>

    @php
        $links = [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
            ['route' => 'admin.products.index', 'label' => 'Products', 'active' => 'admin.products.*', 'icon' => 'cube'],
            ['route' => 'admin.categories.index', 'label' => 'Categories', 'active' => 'admin.categories.*', 'icon' => 'squares'],
            ['route' => 'admin.orders.index', 'label' => 'Orders', 'active' => 'admin.orders.*', 'icon' => 'receipt'],
            ['route' => 'admin.customers.index', 'label' => 'Customers', 'active' => 'admin.customers.*', 'icon' => 'users'],
            ['route' => 'admin.inventory.index', 'label' => 'Inventory', 'active' => 'admin.inventory.*', 'icon' => 'archive'],
            ['route' => 'admin.settings.edit', 'label' => 'Settings', 'active' => 'admin.settings.*', 'icon' => 'cog'],
        ];
    @endphp

    <ul class="flex flex-col gap-1">
        @foreach ($links as $link)
            @php($isActive = request()->routeIs($link['active']))
            <li>
                <a
                    href="{{ route($link['route']) }}"
                    @if ($isActive) aria-current="page" @endif
                    class="group flex items-center gap-3 rounded-lg px-2 py-2 text-sm transition-colors {{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/10 text-white' : 'text-slate-400 group-hover:text-white' }}">
                        @switch($link['icon'])
                            @case('home')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5.25v-6.75h-5.5V21H4a1 1 0 0 1-1-1v-9.5Z" stroke-linejoin="round" />
                                </svg>
                                @break
                            @case('cube')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9L12 21.75M3 7.5l9 5.25M3 7.5v9L12 21.75m0-9.5v9.5" stroke-linejoin="round" />
                                </svg>
                                @break
                            @case('squares')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" stroke-linejoin="round" />
                                </svg>
                                @break
                            @case('receipt')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M9 12h3.75M9 15h3.75M9 9h.75M15 12h.01M15 15h.01M15 9h.01M7.5 21 3 17.25V4.5A2.25 2.25 0 0 1 5.25 2.25h13.5A2.25 2.25 0 0 1 21 4.5v12.75L16.5 21H7.5Z" stroke-linejoin="round" />
                                </svg>
                                @break
                            @case('users')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M15 19.125a9.003 9.003 0 0 0 8.625-9.75 9.005 9.005 0 0 0-7.093-8.341c-.28-.082-.521.227-.297.493l.539.83c.498.77.872 1.58 1.123 2.421.2.657-.2 1.3-.851 1.535a9.01 9.01 0 0 1-8.092 0c-.651-.235-1.05-.878-.85-1.535.25-.841.625-1.65 1.123-2.421l.539-.83c.224-.266-.017-.575-.297-.493A9.005 9.005 0 0 0 2.25 9.375c0 5.16 3.52 9.49 8.25 10.75a.25.25 0 0 0 .25-.25v-1.37a2.25 2.25 0 0 1 1.03-1.896l.97-.592a2.25 2.25 0 0 0 0-3.886l-.97-.592a2.25 2.25 0 0 1-1.03-1.896V5.25" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                @break
                            @case('archive')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" stroke-linejoin="round" />
                                </svg>
                                @break
                            @case('cog')
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                @break
                        @endswitch
                    </span>
                    <span class="flex-1 truncate">{{ $link['label'] }}</span>
                    @if ($isActive)
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>

