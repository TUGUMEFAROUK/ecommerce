@props(['category'])

@php
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('public');
    $path = null;

    if (filled($category->image) && $disk->exists($category->image)) {
        $path = $category->image;
    } else {
        $product = $category->products->firstWhere(fn ($item) => $item->images->isNotEmpty());
        $fallback = $product?->images->first()?->path;

        if ($fallback !== null && $disk->exists($fallback)) {
            $path = $fallback;
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden bg-mist']) }}>
    @if ($path !== null)
        <img
            src="{{ $disk->url($path) }}"
            alt="{{ $category->name }} collection"
            loading="lazy"
            decoding="async"
            class="h-full w-full object-cover"
        >
    @else
        <img
            src="{{ asset('images/product-placeholder.svg') }}"
            alt="{{ $category->name }} collection (no image available)"
            loading="lazy"
            decoding="async"
            class="h-full w-full object-contain p-8 opacity-80"
        >
    @endif
</div>
