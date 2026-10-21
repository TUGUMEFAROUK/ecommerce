@props(['title' => null])

<x-layouts.shop :title="$title">
    {{ $slot }}
</x-layouts.shop>
