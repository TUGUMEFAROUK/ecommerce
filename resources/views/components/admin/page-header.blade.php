@props(['title'])

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
