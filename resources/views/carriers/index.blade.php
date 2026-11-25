<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Carriers') }}</h2>
            <a href="{{ route('carriers.create') }}"
               class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                New carrier
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
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
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Code</th>
                            <th class="py-2 pr-4">Mode</th>
                            <th class="py-2 pr-4">Delay threshold</th>
                            <th class="py-2 pr-4">Shipments</th>
                            <th class="py-2 pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($carriers as $carrier)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $carrier->name }}</td>
                                <td class="py-2 pr-4">{{ $carrier->code }}</td>
                                <td class="py-2 pr-4">{{ $carrier->mode }}</td>
                                <td class="py-2 pr-4">{{ $carrier->delay_threshold_hours !== null ? $carrier->delay_threshold_hours.' h' : 'default' }}</td>
                                <td class="py-2 pr-4">{{ $carrier->shipments_count }}</td>
                                <td class="py-2 pr-4">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('carriers.edit', $carrier) }}" class="text-indigo-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('carriers.destroy', $carrier) }}"
                                              onsubmit="return confirm('Delete this carrier?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-500">No carriers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $carriers->links() }}</div>
            </div>

            <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:underline text-sm">&larr; Back to dashboard</a>
        </div>
    </div>
</x-app-layout>