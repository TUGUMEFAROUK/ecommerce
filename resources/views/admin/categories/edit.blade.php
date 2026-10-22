<x-layouts.admin :title="'Edit '.$category->name">
    <x-admin.page-header :title="'Edit '.$category->name" description="Update the category name, parent and imagery.">
        <x-admin.button variant="secondary" :href="route('admin.categories.index')">Back to categories</x-admin.button>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.categories._form', ['category' => $category])

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-admin.button type="submit">Save changes</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.categories.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-layouts.admin>
