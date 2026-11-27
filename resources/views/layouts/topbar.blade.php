<div class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
    <button type="button" @click="sidebar = true" class="-ml-2 p-2 text-gray-500 hover:text-gray-700 lg:hidden" aria-label="Open menu">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="hidden text-sm text-gray-500 lg:block">{{ now()->format('l, d F Y') }}</div>

    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button type="button" class="flex items-center gap-3 text-sm text-gray-700 focus:outline-none">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">
                    {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="hidden text-left leading-tight sm:block">
                    <span class="block font-medium">{{ Auth::user()->name }}</span>
                    <span class="block text-xs capitalize text-gray-500">{{ Auth::user()->role }}</span>
                </span>
                <svg class="h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
</div>