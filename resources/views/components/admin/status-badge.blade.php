@props(['status', 'map' => null])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

    $tones = $map ?? [
        'active' => 'emerald',
        'archived' => 'slate',
        'cancelled' => 'slate',
        'delivered' => 'emerald',
        'draft' => 'slate',
        'failed' => 'rose',
        'paid' => 'emerald',
        'pending' => 'amber',
        'processing' => 'sky',
        'refunded' => 'rose',
        'shipped' => 'sky',
        'succeeded' => 'emerald',
    ];
@endphp

<x-admin.badge :tone="$tones[$value] ?? 'slate'">{{ \Illuminate\Support\Str::headline($value) }}</x-admin.badge>
