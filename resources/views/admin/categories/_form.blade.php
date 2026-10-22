@php($category = $category ?? null)

<div class="admin-card max-w-2xl space-y-5 p-5">
    <div>
        <label for="name" class="admin-label">Name</label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $category?->name) }}"
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
            value="{{ old('slug', $category?->slug) }}"
            required
            maxlength="255"
            placeholder="lowercase-with-dashes"
            class="admin-field"
        >
        @error('slug') <p class="admin-error">{{ $message }}</p> @enderror
        <p class="admin-help">Lowercase letters, numbers and single dashes only.</p>
    </div>

    <div>
        <label for="parent_id" class="admin-label">Parent category</label>
        <select
            id="parent_id"
            name="parent_id"
            class="admin-field"
        >
            <option value="">None (top-level category)</option>
            @foreach ($parents as $parent)
                <option
                    value="{{ $parent->id }}"
                    @selected((string) old('parent_id', $category?->parent_id) === (string) $parent->id)
                >
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id') <p class="admin-error">{{ $message }}</p> @enderror
        @error('category') <p class="admin-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="image_upload" class="admin-label">Category image</label>
        <input
            id="image_upload"
            type="file"
            name="image_upload"
            accept="image/jpeg,image/png,image/webp"
            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
        >
        @error('image_upload') <p class="admin-error">{{ $message }}</p> @enderror

        @if ($category?->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($category->image))
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image) }}"
                alt="Current category image"
                class="mt-3 h-32 w-auto rounded-lg border border-slate-200 object-cover"
            >
            <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                Remove this image
            </label>
        @endif

        <p class="admin-help">Optional. Without an image the storefront shows the first product photo from this category.</p>
    </div>
</div>
