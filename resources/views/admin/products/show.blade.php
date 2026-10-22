<x-layouts.admin :title="$product->name">
    <x-admin.page-header :title="$product->name" :description="$product->sku">
        <x-admin.button :href="route('admin.products.edit', $product)">Edit</x-admin.button>
        <x-admin.button variant="secondary" :href="route('admin.products.index')">Back to products</x-admin.button>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="admin-card p-5">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Details</h2>

            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex justify-between gap-4 py-3 first:pt-0">
                    <dt class="text-slate-500">Category</dt>
                    <dd class="font-medium text-slate-900">{{ $product->category?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Price</dt>
                    <dd class="font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($product->price) }}</dd>
                </div>
                @if ($product->sale_price !== null)
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-slate-500">Sale price</dt>
                        <dd class="font-medium text-emerald-600">{{ \App\Support\Money::formatWithCode($product->sale_price) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Stock</dt>
                    <dd class="font-medium {{ $product->stock_quantity === 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $product->stock_quantity }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Status</dt>
                    <dd><x-admin.status-badge :status="$product->status" /></dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Featured</dt>
                    <dd class="font-medium text-slate-900">{{ $product->featured ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3 last:pb-0">
                    <dt class="text-slate-500">Created</dt>
                    <dd class="font-medium text-slate-900">{{ $product->created_at?->format('M j, Y H:i') }}</dd>
                </div>
            </dl>

            @if (filled($product->description))
                <h3 class="mb-2 mt-6 text-sm font-semibold text-slate-900">Description</h3>
                <p class="whitespace-pre-line text-sm text-slate-600">{{ $product->description }}</p>
            @endif

            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p>
                    Stock adjustments are recorded on the
                    <a href="{{ route('admin.inventory.index') }}" class="font-medium text-slate-900 underline">inventory page</a>
                    with an audit trail.
                </p>
            </div>
        </section>

        <section class="admin-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900">Images</h2>
                <span class="text-sm text-slate-500">{{ $product->images->count() }} {{ Str::plural('image', $product->images->count()) }}</span>
            </div>

            <form
                method="POST"
                action="{{ route('admin.products.images.store', $product) }}"
                enctype="multipart/form-data"
                class="mb-6 rounded-lg border border-dashed border-slate-300 p-4"
            >
                @csrf

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="image" class="admin-label">Upload an image</label>
                        <input
                            id="image"
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                        >
                        @error('image') <p class="admin-error">{{ $message }}</p> @enderror
                        <p class="admin-help">JPG, PNG or WebP up to 2 MB. The first image becomes the primary image.</p>
                    </div>

                    <x-admin.button type="submit">Upload</x-admin.button>
                </div>
            </form>

            @if ($product->images->isEmpty())
                <x-admin.empty-state
                    title="No images yet"
                    message="Uploaded images appear here and on the storefront product page."
                />
            @else
                <ul class="grid gap-4 sm:grid-cols-2">
                    @foreach ($product->images as $image)
                        <li class="overflow-hidden rounded-lg border border-slate-200">
                            <div class="aspect-square overflow-hidden bg-slate-50">
                                <img
                                    src="{{ Storage::disk('public')->url($image->path) }}"
                                    alt="{{ $product->name }} image {{ $image->position + 1 }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover"
                                >
                            </div>

                            <div class="space-y-2 p-3">
                                @if ($image->is_primary)
                                    <x-admin.badge tone="emerald">Primary</x-admin.badge>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('admin.images.replace', $image) }}"
                                    enctype="multipart/form-data"
                                    class="flex items-center gap-2"
                                >
                                    @csrf
                                    @method('PUT')

                                    <label for="replace-{{ $image->id }}" class="sr-only">Replacement file for image {{ $image->position + 1 }}</label>
                                    <input
                                        id="replace-{{ $image->id }}"
                                        type="file"
                                        name="image"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        required
                                        class="min-w-0 flex-1 text-xs text-slate-600"
                                    >
                                    <x-admin.button type="submit" size="sm" variant="secondary">Replace</x-admin.button>
                                </form>

                                <form method="POST" action="{{ route('admin.images.destroy', $image) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-admin.confirm-button
                                        variant="danger"
                                        message="Delete this image? The file is removed from storage."
                                    >
                                        Delete image
                                    </x-admin.confirm-button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.admin>
