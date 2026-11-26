@props(['status'])

@php
    $map = [
        'CREATED' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'BOOKED' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'PICKED_UP' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'IN_TRANSIT' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'DELIVERED' => 'bg-green-50 text-green-700 ring-green-600/20',
        'OPEN' => 'bg-red-50 text-red-700 ring-red-600/20',
        'INVESTIGATING' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'RESOLVED' => 'bg-green-50 text-green-700 ring-green-600/20',
        'LOW' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
        'MEDIUM' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        'HIGH' => 'bg-red-50 text-red-700 ring-red-600/20',
        'DELAYED' => 'bg-red-50 text-red-700 ring-red-600/20',
        'ON TIME' => 'bg-green-50 text-green-700 ring-green-600/20',
        'EXCEPTION' => 'bg-red-50 text-red-700 ring-red-600/20',
    ];

    $classes = $map[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$classes]) }}>
    {{ str_replace('_', ' ', $status) }}
</span>