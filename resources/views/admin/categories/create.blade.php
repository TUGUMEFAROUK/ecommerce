<x-layouts.admin title="New category">
    <x-admin.page-header title="New category">
        <a href="{{ route('admin.categories.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Back to categories
        </a>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

        @include('admin.categories._form')

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Create category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</x-layouts.admin>
