@php($category = $category ?? null)

<div class="max-w-2xl space-y-4 rounded border border-gray-200 bg-white p-6">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $category?->name) }}"
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
            value="{{ old('slug', $category?->slug) }}"
            required
            maxlength="255"
            placeholder="lowercase-with-dashes"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
        >
        @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers and single dashes only.</p>
    </div>

    <div>
        <label for="parent_id" class="mb-1 block text-sm font-medium text-gray-700">Parent category</label>
        <select
            id="parent_id"
            name="parent_id"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
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
        @error('parent_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
