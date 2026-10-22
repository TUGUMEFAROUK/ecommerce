<x-layouts.admin title="New product">
    <x-admin.page-header title="New product" description="Add a product to the catalogue.">
        <x-admin.button variant="secondary" :href="route('admin.products.index')">Back to products</x-admin.button>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        @include('admin.products._form')

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-admin.button type="submit">Create product</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.products.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-layouts.admin>
