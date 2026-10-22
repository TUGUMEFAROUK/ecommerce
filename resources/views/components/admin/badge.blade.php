@props(['tone' => 'slate'])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10',
        'amber' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/10',
        'rose' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/10',
        'sky' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/10',
        'slate' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/10',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'admin-badge '.($tones[$tone] ?? $tones['slate'])]) }}>{{ $slot }}</span>
