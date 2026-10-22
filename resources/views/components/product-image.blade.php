@props(['product', 'image' => null, 'alt' => null, 'eager' => false])

@php
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('public');
    $image ??= $product->images->first();
    $hasImage = $image !== null && $disk->exists($image->path);
    $alt ??= $product->name;
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden bg-mist']) }}>
    @if ($hasImage)
        <img
            src="{{ $disk->url($image->path) }}"
            alt="{{ $alt }}"
            @if (! $eager) loading="lazy" @else loading="eager" @endif
            decoding="async"
            class="h-full w-full object-cover"
        >
    @else
        <img
            src="{{ asset('images/product-placeholder.svg') }}"
            alt="{{ $alt }} (no image available)"
            loading="lazy"
            decoding="async"
            class="h-full w-full object-contain p-6 opacity-80"
        >
    @endif
</div>
