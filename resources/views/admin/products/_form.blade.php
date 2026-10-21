@php($product = $product ?? null)

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4 rounded border border-gray-200 bg-white p-6">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $product?->name) }}"
                required
                maxlength="255"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="mb-1 block text-sm font-medium text-gray-700">Slug</label>
            <input
                id="slug"
                type="text"
                name="slug"
                value="{{ old('slug', $product?->slug) }}"
                required
                maxlength="255"
                placeholder="lowercase-with-dashes"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
            @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers and single dashes only.</p>
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea
                id="description"
                name="description"
                rows="5"
                maxlength="10000"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >{{ old('description', $product?->description) }}</textarea>
            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="space-y-4 rounded border border-gray-200 bg-white p-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="price" class="mb-1 block text-sm font-medium text-gray-700">Price</label>
                <input
                    id="price"
                    type="number"
                    name="price"
                    value="{{ old('price', $product?->price) }}"
                    required
                    min="0"
                    step="0.01"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                >
                @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="stock_quantity" class="mb-1 block text-sm font-medium text-gray-700">Stock quantity</label>
                <input
                    id="stock_quantity"
                    type="number"
                    name="stock_quantity"
                    value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}"
                    required
                    min="0"
                    step="1"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                >
                @error('stock_quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="sku" class="mb-1 block text-sm font-medium text-gray-700">SKU</label>
            <input
                id="sku"
                type="text"
                name="sku"
                value="{{ old('sku', $product?->sku) }}"
                required
                maxlength="100"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
            >
            @error('sku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">Category</label>
            <select
                id="category_id"
                name="category_id"
                required
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
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
            @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select
                id="status"
                name="status"
                required
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
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
            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Archived products are hidden from the storefront.</p>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="featured" value="0">
            <input
                id="featured"
                type="checkbox"
                name="featured"
                value="1"
                @checked(old('featured', $product?->featured ?? false))
                class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
            >
            <label for="featured" class="text-sm font-medium text-gray-700">Featured on the homepage</label>
        </div>
        @error('featured') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
