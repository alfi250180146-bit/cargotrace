@php
    $user = Auth::user();

    $icons = [
        'dashboard' => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>',
        'shipments' => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'carriers' => '<path d="M1 6h13v10H1z"/><path d="M14 9h4l3 3v4h-7z"/><circle cx="6" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'locations' => '<path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'settings' => '<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/>',
    ];

    $sections = [
        'Menu' => [
            ['Dashboard', route('dashboard'), request()->routeIs('dashboard'), 'dashboard'],
            ['Shipments', route('shipments.index'), request()->routeIs('shipments.*'), 'shipments'],
        ],
        'Administration' => $user->hasRole('admin') ? [
            ['Carriers', route('carriers.index'), request()->routeIs('carriers.*'), 'carriers'],
            ['Locations', route('locations.index'), request()->routeIs('locations.*'), 'locations'],
            ['Delay settings', route('settings.edit'), request()->routeIs('settings.*'), 'settings'],
        ] : [],
    ];
@endphp

<div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-brand-900 text-white transition-transform duration-200 -translate-x-full lg:translate-x-0"
    :class="{ 'translate-x-0': sidebar, '-translate-x-full': !sidebar }"
>
    <a href="{{ route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-brand-800 px-5">
        <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 8l-9-5-9 5 9 5 9-5z" />
            <path d="M3 8v8l9 5 9-5V8" />
            <path d="M12 13v8" />
        </svg>
        <span>
            <span class="block text-base font-semibold leading-tight">CargoTrace</span>
            <span class="block text-xs text-brand-300 leading-tight">Shipment tracking</span>
        </span>
    </a>

    <nav class="flex-1 overflow-y-auto py-4">
        @foreach ($sections as $title => $items)
            @if (count($items) > 0)
                <div class="px-5 {{ $loop->first ? '' : 'mt-6' }} mb-2 text-xs font-semibold uppercase tracking-wider text-brand-300">{{ $title }}</div>

                @foreach ($items as [$label, $url, $active, $icon])
                    <a href="{{ $url }}"
                       class="flex items-center gap-3 border-l-4 px-5 py-2.5 text-sm font-medium {{ $active ? 'border-white bg-brand-800 text-white' : 'border-transparent text-brand-100 hover:bg-brand-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$icon] !!}</svg>
                        {{ $label }}
                    </a>
                @endforeach
            @endif
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-brand-800 px-5 py-4 text-xs text-brand-300">
        Signed in as <span class="font-semibold text-white">{{ $user->role }}</span>
    </div>
</aside>