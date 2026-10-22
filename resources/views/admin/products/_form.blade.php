@php($product = $product ?? null)
@php($images = $product?->images ?? collect())

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-6">
        <section class="admin-card p-5">
            <h2 class="text-base font-semibold text-slate-900">Product details</h2>
            <p class="mt-1 text-sm text-slate-500">The name and description customers read on the storefront.</p>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="name" class="admin-label">Name</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $product?->name) }}"
                        required
                        maxlength="255"
                        class="admin-field"
                    >
                    @error('name') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="admin-label">Slug</label>
                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        value="{{ old('slug', $product?->slug) }}"
                        required
                        maxlength="255"
                        placeholder="lowercase-with-dashes"
                        class="admin-field"
                    >
                    @error('slug') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Lowercase letters, numbers and single dashes only.</p>
                </div>

                <div>
                    <label for="description" class="admin-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        maxlength="10000"
                        class="admin-textarea"
                    >{{ old('description', $product?->description) }}</textarea>
                    @error('description') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="admin-card p-5">
            <h2 class="text-base font-semibold text-slate-900">Imagery</h2>
            <p class="mt-1 text-sm text-slate-500">The first image is used as the product thumbnail.</p>

            @if ($images->isNotEmpty())
                <ul class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($images as $image)
                        <li class="overflow-hidden rounded-lg border border-slate-200">
                            <img
                                src="{{ Storage::disk('public')->url($image->path) }}"
                                alt="{{ $product?->name }} image {{ $image->position + 1 }}"
                                loading="lazy"
                                class="aspect-square w-full object-cover"
                            >
                            @if ($image->is_primary)
                                <span class="block bg-slate-900 px-2 py-1 text-[11px] font-medium uppercase tracking-wider text-white">Primary</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-5">
                <label for="image_upload" class="admin-label">{{ $product ? 'Replace the primary image' : 'Product image' }}</label>
                <input
                    id="image_upload"
                    type="file"
                    name="image_upload"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                >
                @error('image_upload') <p class="admin-error">{{ $message }}</p> @enderror
                <p class="admin-help">JPG, PNG or WebP up to 2 MB. Optional — you can also upload images later from the product page.</p>
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="admin-card p-5">
            <h2 class="text-base font-semibold text-slate-900">Pricing &amp; stock</h2>
            <p class="mt-1 text-sm text-slate-500">How much the product costs and how many are available.</p>

            <div class="mt-5 space-y-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="price" class="admin-label">Price</label>
                        <input
                            id="price"
                            type="number"
                            name="price"
                            value="{{ old('price', $product?->price) }}"
                            required
                            min="0"
                            max="99999999.99"
                            step="{{ \App\Support\Money::decimals() > 0 ? '0.01' : '1' }}"
                            class="admin-field"
                        >
                        @error('price') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="sale_price" class="admin-label">Sale price</label>
                        <input
                            id="sale_price"
                            type="number"
                            name="sale_price"
                            value="{{ old('sale_price', $product?->sale_price) }}"
                            min="0"
                            max="99999999.99"
                            step="{{ \App\Support\Money::decimals() > 0 ? '0.01' : '1' }}"
                            class="admin-field"
                        >
                        @error('sale_price') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="stock_quantity" class="admin-label">Stock quantity</label>
                        <input
                            id="stock_quantity"
                            type="number"
                            name="stock_quantity"
                            value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}"
                            required
                            min="0"
                            step="1"
                            class="admin-field"
                        >
                        @error('stock_quantity') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="admin-help mt-0">
                    Leave the sale price empty for a full-price product. A sale price must be lower than the list price.
                </p>

                <div>
                    <label for="sku" class="admin-label">SKU</label>
                    <input
                        id="sku"
                        type="text"
                        name="sku"
                        value="{{ old('sku', $product?->sku) }}"
                        required
                        maxlength="100"
                        class="admin-field"
                    >
                    @error('sku') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="admin-card p-5">
            <h2 class="text-base font-semibold text-slate-900">Organisation</h2>
            <p class="mt-1 text-sm text-slate-500">Where the product sits in the catalogue.</p>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="category_id" class="admin-label">Category</label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="admin-field"
                    >
                        <option value="">Choose a category</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) old('category_id', $product?->category_id) === (string) $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="admin-label">Status</label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="admin-field"
                    >
                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status->value }}"
                                @selected(old('status', $product?->status?->value ?? 'active') === $status->value)
                            >
                                {{ Str::headline($status->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Archived products are hidden from the storefront.</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="featured" value="0">
                    <input
                        id="featured"
                        type="checkbox"
                        name="featured"
                        value="1"
                        @checked(old('featured', $product?->featured ?? false))
                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                    >
                    <label for="featured" class="text-sm font-medium text-slate-700">Featured on the homepage</label>
                </div>
                @error('featured') <p class="admin-error">{{ $message }}</p> @enderror
            </div>
        </section>
    </div>
</div>
