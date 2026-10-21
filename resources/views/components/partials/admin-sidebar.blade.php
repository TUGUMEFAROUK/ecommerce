<nav aria-label="Admin" class="flex flex-1 flex-col gap-1 p-4">
    <p class="px-3 pb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Admin panel</p>

    @php($links = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'active' => 'admin.dashboard'],
        ['route' => 'admin.products.index', 'label' => 'Products', 'active' => 'admin.products.*'],
        ['route' => 'admin.categories.index', 'label' => 'Categories', 'active' => 'admin.categories.*'],
        ['route' => 'admin.orders.index', 'label' => 'Orders', 'active' => 'admin.orders.*'],
        ['route' => 'admin.customers.index', 'label' => 'Customers', 'active' => 'admin.customers.*'],
        ['route' => 'admin.inventory.index', 'label' => 'Inventory', 'active' => 'admin.inventory.*'],
        ['route' => 'admin.settings.edit', 'label' => 'Settings', 'active' => 'admin.settings.*'],
    ])

    @foreach ($links as $link)
        @php($isActive = request()->routeIs($link['active']))
        <a
            href="{{ route($link['route']) }}"
            @if ($isActive) aria-current="page" @endif
            class="rounded px-3 py-2 text-sm {{ $isActive ? 'bg-gray-800 font-semibold text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
        >
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
