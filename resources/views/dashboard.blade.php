<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <p>Welcome, {{ auth()->user()->name }} ({{ auth()->user()->role }}).</p>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('shipments.index') }}" class="text-indigo-600 hover:underline">
                            Go to shipments &rarr;
                        </a>

                        @if (auth()->user()->hasRole('admin'))
                            <a href="{{ route('settings.edit') }}" class="text-indigo-600 hover:underline">
                                Delay settings &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>