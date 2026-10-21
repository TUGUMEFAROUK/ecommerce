@props(['label', 'value', 'meta' => null])

<div {{ $attributes->merge(['class' => 'rounded border border-gray-200 bg-white p-6']) }}>
    <p class="text-sm text-gray-600">{{ $label }}</p>
    <p class="mt-1 text-2xl font-semibold">{{ $value }}</p>

    @if (filled($meta))
        <p class="mt-1 text-xs text-gray-500">{{ $meta }}</p>
    @endif
</div>
