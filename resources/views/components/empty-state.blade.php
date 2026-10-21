@props(['title', 'message'])

<div {{ $attributes->merge(['class' => 'rounded border border-dashed border-gray-300 bg-white px-6 py-12 text-center']) }}>
    <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm text-gray-600">{{ $message }}</p>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
