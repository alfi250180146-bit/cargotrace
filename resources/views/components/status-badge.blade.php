@props(['status'])

@php
    $map = [
        'CREATED' => 'bg-stone-100 text-stone-700 border-stone-200',
        'BOOKED' => 'bg-sky-100 text-sky-800 border-sky-200',
        'PICKED_UP' => 'bg-amber-100 text-amber-800 border-amber-200',
        'IN_TRANSIT' => 'bg-teal-100 text-teal-800 border-teal-200',
        'DELIVERED' => 'bg-green-100 text-green-800 border-green-200',
        'OPEN' => 'bg-rose-100 text-rose-800 border-rose-200',
        'INVESTIGATING' => 'bg-sky-100 text-sky-800 border-sky-200',
        'RESOLVED' => 'bg-green-100 text-green-800 border-green-200',
        'LOW' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'MEDIUM' => 'bg-orange-100 text-orange-800 border-orange-200',
        'HIGH' => 'bg-rose-100 text-rose-800 border-rose-200',
        'DELAYED' => 'bg-rose-100 text-rose-800 border-rose-200',
        'ON TIME' => 'bg-green-100 text-green-800 border-green-200',
        'EXCEPTION' => 'bg-rose-100 text-rose-800 border-rose-200',
    ];

    $classes = $map[$status] ?? 'bg-stone-100 text-stone-700 border-stone-200';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded border px-2 py-0.5 text-xs font-semibold uppercase tracking-wide '.$classes]) }}>
    {{ str_replace('_', ' ', $status) }}
</span>