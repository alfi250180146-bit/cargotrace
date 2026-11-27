@props(['label', 'value', 'accent' => 'brand'])

@php
    $tops = [
        'brand' => 'border-t-brand-600',
        'indigo' => 'border-t-brand-600',
        'green' => 'border-t-green-600',
        'amber' => 'border-t-amber-500',
        'red' => 'border-t-red-600',
        'sky' => 'border-t-sky-500',
        'slate' => 'border-t-slate-400',
    ];
@endphp

<div class="bg-white border border-gray-200 border-t-4 {{ $tops[$accent] ?? $tops['brand'] }} rounded-md p-5">
    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $value }}</div>
</div>