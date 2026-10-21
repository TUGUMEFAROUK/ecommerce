<x-layouts.admin :title="'Edit '.$product->name">
    <x-admin.page-header :title="'Edit '.$product->name">
        <a href="{{ route('admin.products.show', $product) }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            View product
        </a>
        <a href="{{ route('admin.products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Back to products
        </a>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.products.update', $product) }}">
        @csrf
        @method('PUT')

        @include('admin.products._form', ['product' => $product])

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Save changes
            </button>
            <a href="{{ route('admin.products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</x-layouts.admin>
