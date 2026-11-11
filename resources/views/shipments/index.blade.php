<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shipments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-gray-600 border-b">
                        <tr>
                            <th class="py-2 pr-4">Shipment No</th>
                            <th class="py-2 pr-4">Customer</th>
                            <th class="py-2 pr-4">Carrier</th>
                            <th class="py-2 pr-4">Route</th>
                            <th class="py-2 pr-4">Expected arrival</th>
                            <th class="py-2 pr-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shipments as $shipment)
                            <tr class="border-b">
                                <td class="py-2 pr-4">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="text-indigo-600 hover:underline">
                                        {{ $shipment->shipment_number }}
                                    </a>
                                </td>
                                <td class="py-2 pr-4">{{ $shipment->customer->name }}</td>
                                <td class="py-2 pr-4">{{ $shipment->carrier->name }}</td>
                                <td class="py-2 pr-4">{{ $shipment->origin->code }} &rarr; {{ $shipment->destination->code }}</td>
                                <td class="py-2 pr-4">{{ $shipment->expected_arrival->format('d M Y H:i') }}</td>
                                <td class="py-2 pr-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ $shipment->current_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-500">No shipments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $shipments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>