<x-layouts.admin :title="$product->name">
    <x-admin.page-header :title="$product->name">
        <a href="{{ route('admin.products.edit', $product) }}" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
            Edit
        </a>
        <a href="{{ route('admin.products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Back to products
        </a>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold">Details</h2>

            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">SKU</dt>
                    <dd class="font-medium">{{ $product->sku }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">Category</dt>
                    <dd class="font-medium">{{ $product->category?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">Price</dt>
                    <dd class="font-medium">{{ number_format((float) $product->price, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">Stock</dt>
                    <dd class="font-medium {{ $product->stock_quantity === 0 ? 'text-red-600' : '' }}">{{ $product->stock_quantity }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">Status</dt>
                    <dd class="font-medium">{{ Str::headline($product->status->value) }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">Featured</dt>
                    <dd class="font-medium">{{ $product->featured ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-600">Created</dt>
                    <dd class="font-medium">{{ $product->created_at?->format('M j, Y H:i') }}</dd>
                </div>
            </dl>

            @if (filled($product->description))
                <h3 class="mb-2 mt-6 text-sm font-semibold text-gray-900">Description</h3>
                <p class="whitespace-pre-line text-sm text-gray-600">{{ $product->description }}</p>
            @endif

            <div class="mt-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                <p>
                    Stock adjustments are recorded on the
                    <a href="{{ route('admin.inventory.index') }}" class="underline">inventory page</a>
                    with an audit trail.
                </p>
            </div>
        </section>

        <section class="rounded border border-gray-200 bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Images</h2>
                <span class="text-xs text-gray-500">{{ $product->images->count() }} image(s)</span>
            </div>

            <form
                method="POST"
                action="{{ route('admin.products.images.store', $product) }}"
                enctype="multipart/form-data"
                class="mb-6 flex flex-wrap items-end gap-3 rounded border border-dashed border-gray-300 p-4"
            >
                @csrf

                <div class="flex-1">
                    <label for="image" class="mb-1 block text-sm font-medium text-gray-700">Upload an image</label>
                    <input
                        id="image"
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                        class="w-full text-sm text-gray-700"
                    >
                    @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG or WebP up to 2 MB. The first image becomes the primary image.</p>
                </div>

                <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                    Upload
                </button>
            </form>

            @if ($product->images->isEmpty())
                <x-empty-state title="No images yet" message="Uploaded images appear here and on the storefront product page." />
            @else
                <ul class="grid gap-4 sm:grid-cols-2">
                    @foreach ($product->images as $image)
                        <li class="rounded border border-gray-200 p-3">
                            <div class="mb-2 aspect-square overflow-hidden rounded bg-gray-100">
                                <img
                                    src="{{ Storage::disk('public')->url($image->path) }}"
                                    alt="{{ $product->name }} image {{ $image->position + 1 }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover"
                                >
                            </div>

                            @if ($image->is_primary)
                                <span class="mb-2 inline-block rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Primary</span>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('admin.images.replace', $image) }}"
                                enctype="multipart/form-data"
                                class="mb-2 flex gap-2"
                            >
                                @csrf
                                @method('PUT')

                                <input
                                    type="file"
                                    name="image"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    required
                                    class="min-w-0 flex-1 text-xs text-gray-700"
                                >
                                <button type="submit" class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-50">
                                    Replace
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.images.destroy', $image) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Delete image</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.admin>
