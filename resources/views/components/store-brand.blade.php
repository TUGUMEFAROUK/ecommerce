@props([
    'nameClass' => 'font-display text-xl leading-none tracking-tight text-charcoal lg:text-2xl',
    'showName' => true,
    'variant' => 'light',
    'rounded' => 'rounded-sm',
    'size' => 'h-9 w-9',
    'maxHeight' => null,
])

@php
    $settings = app(\App\Services\SettingsService::class);
    $storeName = $settings->get('store_name');
    $logoPath = $settings->get('store_logo');
    $logoUrl = filled($logoPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath)
        : null;
    $useLogo = $settings->usesLogo() && $logoUrl !== null;
    $logoHeight = $maxHeight === null
        ? $settings->logoHeight()
        : min($settings->logoHeight(), (int) $maxHeight);
@endphp

@if ($useLogo)
    <img
        src="{{ $logoUrl }}"
        alt="{{ $storeName }}"
        style="height: {{ $logoHeight }}px"
        class="w-auto max-w-[12rem] object-contain"
    >
@else
    <span
        class="flex {{ $size }} shrink-0 items-center justify-center {{ $rounded }} {{ $variant === 'dark' ? 'bg-white/10 text-white' : 'bg-charcoal text-white' }} text-sm font-medium uppercase tracking-[0.1em]"
        aria-hidden="true"
    >
        {{ strtoupper(substr($storeName, 0, 1)) }}
    </span>

    @if ($showName)
        <span class="{{ $nameClass }}">{{ $storeName }}</span>
    @endif
@endif
