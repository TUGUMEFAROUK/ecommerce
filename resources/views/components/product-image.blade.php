@props(['product'])

@php
    $image = $product->images->first();
    $hasImage = $image !== null && \Illuminate\Support\Facades\Storage::disk('public')->exists($image->path);
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden bg-gray-100']) }}>
    @if ($hasImage)
        <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
            alt="{{ $product->name }}"
            loading="lazy"
            class="h-full w-full object-cover"
        >
    @else
        <img
            src="{{ asset('images/product-placeholder.svg') }}"
            alt="{{ $product->name }} (no image available)"
            loading="lazy"
            class="h-full w-full object-contain p-6 opacity-80"
        >
    @endif
</div>
