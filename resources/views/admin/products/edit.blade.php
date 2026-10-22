<x-layouts.admin :title="'Edit '.$product->name">
    <x-admin.page-header :title="'Edit '.$product->name" description="Update the product details, pricing and imagery.">
        <x-admin.button variant="secondary" :href="route('admin.products.show', $product)">View product</x-admin.button>
        <x-admin.button variant="secondary" :href="route('admin.products.index')">Back to products</x-admin.button>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.products._form', ['product' => $product])

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-admin.button type="submit">Save changes</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.products.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-layouts.admin>
