<x-layouts.admin title="Categories">
    <x-admin.page-header title="Categories">
        <a
            href="{{ route('admin.categories.create') }}"
            class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
        >
            New category
        </a>
    </x-admin.page-header>

    @if ($categories->isEmpty())
        <x-empty-state
            title="No categories yet"
            message="Categories group products in the storefront navigation and catalog filters."
        />
    @else
        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Parent</th>
                        <th class="px-4 py-3 font-medium">Products</th>
                        <th class="px-4 py-3 font-medium">Subcategories</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $category->name }}</span>
                                <p class="text-xs text-gray-500">{{ $category->slug }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $category->products_count }}</td>
                            <td class="px-4 py-3">{{ $category->children_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3 text-sm">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-gray-700 hover:text-gray-900 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    @endif
</x-layouts.admin>
