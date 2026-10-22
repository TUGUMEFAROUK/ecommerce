@props(['title', 'message'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center']) }}>
    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ $message }}</p>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="mt-6 flex flex-wrap justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
