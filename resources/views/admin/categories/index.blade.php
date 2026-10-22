<x-layouts.admin title="Categories">
    <x-admin.page-header title="Categories" description="Categories group products in the storefront navigation and catalog filters.">
        <x-admin.button :href="route('admin.categories.create')">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 5.25v13.5M5.25 12h13.5" stroke-linecap="round" />
            </svg>
            New category
        </x-admin.button>
    </x-admin.page-header>

    @if ($categories->isEmpty())
        <x-admin.empty-state
            title="No categories yet"
            message="Create a category to group products in the storefront navigation and catalog filters."
        >
            <x-admin.button :href="route('admin.categories.create')">New category</x-admin.button>
        </x-admin.empty-state>
    @else
        <div class="admin-table-wrap">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="admin-th">Category</th>
                        <th class="admin-th">Parent</th>
                        <th class="admin-th">Products</th>
                        <th class="admin-th">Subcategories</th>
                        <th class="admin-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="admin-row">
                            <td class="admin-td">
                                <span class="block font-medium text-slate-900">{{ $category->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $category->slug }}</span>
                            </td>
                            <td class="admin-td">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="admin-td">{{ number_format($category->products_count) }}</td>
                            <td class="admin-td">{{ number_format($category->children_count) }}</td>
                            <td class="admin-td">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit</a>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.confirm-button
                                            variant="danger"
                                            message="Delete {{ $category->name }}? Products in this category must be moved first."
                                        >
                                            Delete
                                        </x-admin.confirm-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $categories->links('components.admin.pagination') }}
    @endif
</x-layouts.admin>
