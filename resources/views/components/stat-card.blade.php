@props(['label', 'value', 'accent' => 'indigo'])

@php
    $borders = [
        'indigo' => 'border-indigo-500',
        'green' => 'border-green-500',
        'amber' => 'border-amber-500',
        'red' => 'border-red-500',
        'sky' => 'border-sky-500',
        'slate' => 'border-slate-400',
    ];
@endphp

<div class="bg-white rounded-xl shadow-sm border-l-4 {{ $borders[$accent] ?? $borders['indigo'] }} p-5">
    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $value }}</div>
</div>