@props(['label', 'value', 'accent' => 'brand'])

@php
    $tops = [
        'brand' => 'border-t-brand-400',
        'indigo' => 'border-t-brand-400',
        'green' => 'border-t-green-400',
        'amber' => 'border-t-amber-300',
        'red' => 'border-t-rose-300',
        'sky' => 'border-t-sky-300',
        'slate' => 'border-t-stone-300',
    ];
@endphp

<div class="bg-white border border-stone-200 border-t-4 {{ $tops[$accent] ?? $tops['brand'] }} rounded-md p-5">
    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $value }}</div>
</div>