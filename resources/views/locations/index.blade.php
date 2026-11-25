<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Locations') }}</h2>
            <a href="{{ route('locations.create') }}"
               class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                New location
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 rounded-md bg-green-100 text-green-800 text-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-md bg-red-100 text-red-800 text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-gray-600 border-b">
                        <tr>
                            <th class="py-2 pr-4">Code</th>
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Country</th>
                            <th class="py-2 pr-4">Type</th>
                            <th class="py-2 pr-4">Latitude</th>
                            <th class="py-2 pr-4">Longitude</th>
                            <th class="py-2 pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $location)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $location->code }}</td>
                                <td class="py-2 pr-4">{{ $location->name }}</td>
                                <td class="py-2 pr-4">{{ $location->country }}</td>
                                <td class="py-2 pr-4">{{ $location->type }}</td>
                                <td class="py-2 pr-4">{{ $location->latitude }}</td>
                                <td class="py-2 pr-4">{{ $location->longitude }}</td>
                                <td class="py-2 pr-4">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('locations.edit', $location) }}" class="text-indigo-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('locations.destroy', $location) }}"
                                              onsubmit="return confirm('Delete this location?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-6 text-center text-gray-500">No locations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $locations->links() }}</div>
            </div>

            <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:underline text-sm">&larr; Back to dashboard</a>
        </div>
    </div>
</x-app-layout>