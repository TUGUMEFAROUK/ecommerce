<x-layouts.admin title="New category">
    <x-admin.page-header title="New category" description="Categories group products in the storefront navigation and catalog filters.">
        <x-admin.button variant="secondary" :href="route('admin.categories.index')">Back to categories</x-admin.button>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf

        @include('admin.categories._form')

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-admin.button type="submit">Create category</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.categories.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-layouts.admin>
