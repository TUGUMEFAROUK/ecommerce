@props(['title', 'message'])

<div {{ $attributes->merge(['class' => 'rounded-sm border border-dashed border-sand-deep bg-mist px-6 py-14 text-center']) }}>
    <h2 class="font-display text-2xl text-charcoal">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-charcoal/65">{{ $message }}</p>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="mt-7">
            {{ $slot }}
        </div>
    @endif
</div>
