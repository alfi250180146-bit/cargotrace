@php
    $user = Auth::user();
    $unread = $user->unreadNotifications()->count();

    $links = [
        ['Dashboard', route('dashboard'), request()->routeIs('dashboard')],
        ['Shipments', route('shipments.index'), request()->routeIs('shipments.*')],
    ];

    $adminLinks = $user->hasRole('admin') ? [
        ['Users', route('users.index'), request()->routeIs('users.*')],
        ['Carriers', route('carriers.index'), request()->routeIs('carriers.*')],
        ['Locations', route('locations.index'), request()->routeIs('locations.*')],
        ['Audit log', route('audit-logs.index'), request()->routeIs('audit-logs.*')],
        ['Delay settings', route('settings.edit'), request()->routeIs('settings.*')],
    ] : [];

    $adminActive = collect($adminLinks)->contains(fn ($item) => $item[2]);

    $linkClass = fn (bool $active) => $active
        ? 'border-brand-600 text-brand-900'
        : 'border-transparent text-brand-800 hover:border-brand-400 hover:text-brand-900';
@endphp

<nav x-data="{ mobile: false }" class="bg-brand-100 border-b border-brand-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-stretch justify-between">
            <div class="flex items-stretch gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-brand-900">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8l-9-5-9 5 9 5 9-5z" />
                        <path d="M3 8v8l9 5 9-5V8" />
                        <path d="M12 13v8" />
                    </svg>
                    <span class="text-lg font-semibold">CargoTrace</span>
                </a>

                <div class="hidden md:flex items-stretch gap-6">
                    @foreach ($links as [$label, $url, $active])
                        <a href="{{ $url }}"
                           class="inline-flex items-center border-b-2 px-1 text-sm font-medium {{ $linkClass($active) }}">
                            {{ $label }}
                        </a>
                    @endforeach

                    @if (count($adminLinks) > 0)
                        <div class="relative flex items-stretch" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button type="button" @click="open = ! open"
                                    class="inline-flex items-center gap-1 border-b-2 px-1 text-sm font-medium {{ $linkClass($adminActive) }}">
                                Manage
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak x-transition
                                 class="absolute left-0 top-full z-30 w-48 rounded-md border border-brand-200 bg-white py-1 shadow-md">
                                @foreach ($adminLinks as [$label, $url, $active])
                                    <a href="{{ $url }}"
                                       class="block px-4 py-2 text-sm {{ $active ? 'bg-brand-50 font-medium text-brand-900' : 'text-gray-700 hover:bg-brand-50' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('notifications.index') }}" class="relative p-2 text-brand-800 hover:text-brand-900" aria-label="Notifications">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9a6 6 0 0112 0c0 6 2 7 2 7H4s2-1 2-7" />
                        <path d="M10 20a2 2 0 004 0" />
                    </svg>
                    @if ($unread > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                            {{ $unread > 99 ? '99+' : $unread }}
                        </span>
                    @endif
                </a>

                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" @click="open = ! open" class="flex items-center gap-3 text-sm text-brand-900 focus:outline-none">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">
                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                        </span>
                        <span class="text-left leading-tight">
                            <span class="block font-medium">{{ $user->name }}</span>
                            <span class="block text-xs capitalize text-brand-700">{{ $user->role }}</span>
                        </span>
                        <svg class="h-4 w-4 fill-current text-brand-700" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 top-full z-30 mt-2 w-48 rounded-md border border-brand-200 bg-white py-1 shadow-md">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-50">Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-brand-50">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flex items-center md:hidden">
                <button type="button" @click="mobile = ! mobile" class="p-2 text-brand-800 hover:text-brand-900" aria-label="Open menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path x-show="! mobile" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobile" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobile" x-cloak class="border-t border-brand-200 bg-brand-50 md:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach (array_merge($links, $adminLinks) as [$label, $url, $active])
                <a href="{{ $url }}"
                   class="block rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-brand-100 text-brand-900' : 'text-brand-800 hover:bg-brand-100' }}">
                    {{ $label }}
                </a>
            @endforeach

            <a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-brand-800 hover:bg-brand-100">
                Notifications @if ($unread > 0)<span class="ms-1 rounded-full bg-rose-500 px-1.5 text-xs text-white">{{ $unread }}</span>@endif
            </a>
        </div>

        <div class="border-t border-brand-200 px-4 py-3">
            <div class="text-sm font-medium text-brand-900">{{ $user->name }}</div>
            <div class="text-xs text-brand-700">{{ $user->email }} &middot; {{ $user->role }}</div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-brand-800 hover:bg-brand-100">Profile</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-md px-3 py-2 text-left text-sm text-brand-800 hover:bg-brand-100">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>