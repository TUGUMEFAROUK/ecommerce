@props(['eyebrow' => null, 'heading', 'id' => null, 'link' => null, 'linkLabel' => null])

<div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="max-w-2xl">
        @if (filled($eyebrow))
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif

        <h2 @if ($id) id="{{ $id }}" @endif class="mt-2 font-display text-3xl sm:text-4xl">
            {{ $heading }}
        </h2>
    </div>

    @if (filled($link))
        <a href="{{ $link }}" class="link-quiet shrink-0 text-[0.6875rem] uppercase tracking-[0.18em]">
            {{ $linkLabel ?? 'View all' }}
        </a>
    @endif
</div>
