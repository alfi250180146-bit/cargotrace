@props(['status'])

@php
    $map = [
        'CREATED' => 'bg-slate-50 text-slate-700 border-slate-300',
        'BOOKED' => 'bg-sky-50 text-sky-800 border-sky-200',
        'PICKED_UP' => 'bg-amber-50 text-amber-800 border-amber-200',
        'IN_TRANSIT' => 'bg-teal-50 text-teal-800 border-teal-200',
        'DELIVERED' => 'bg-green-50 text-green-800 border-green-200',
        'OPEN' => 'bg-red-50 text-red-800 border-red-200',
        'INVESTIGATING' => 'bg-sky-50 text-sky-800 border-sky-200',
        'RESOLVED' => 'bg-green-50 text-green-800 border-green-200',
        'LOW' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
        'MEDIUM' => 'bg-orange-50 text-orange-800 border-orange-200',
        'HIGH' => 'bg-red-50 text-red-800 border-red-200',
        'DELAYED' => 'bg-red-50 text-red-800 border-red-200',
        'ON TIME' => 'bg-green-50 text-green-800 border-green-200',
        'EXCEPTION' => 'bg-red-50 text-red-800 border-red-200',
    ];

    $classes = $map[$status] ?? 'bg-slate-50 text-slate-700 border-slate-300';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded border px-2 py-0.5 text-xs font-semibold uppercase tracking-wide '.$classes]) }}>
    {{ str_replace('_', ' ', $status) }}
</span>