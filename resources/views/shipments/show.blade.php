<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shipment') }} {{ $shipment->shipment_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Status</dt><dd class="font-semibold">{{ $shipment->current_status }}</dd></div>
                    <div><dt class="text-gray-500">Customer</dt><dd>{{ $shipment->customer->name }}</dd></div>
                    <div><dt class="text-gray-500">Carrier</dt><dd>{{ $shipment->carrier->name }} ({{ $shipment->carrier->code }})</dd></div>
                    <div><dt class="text-gray-500">Cargo type</dt><dd>{{ $shipment->cargo_type }}</dd></div>
                    <div><dt class="text-gray-500">Origin</dt><dd>{{ $shipment->origin->name }}, {{ $shipment->origin->country }}</dd></div>
                    <div><dt class="text-gray-500">Destination</dt><dd>{{ $shipment->destination->name }}, {{ $shipment->destination->country }}</dd></div>
                    <div><dt class="text-gray-500">Weight</dt><dd>{{ number_format($shipment->weight_kg, 2) }} kg</dd></div>
                    <div><dt class="text-gray-500">Volume</dt><dd>{{ $shipment->volume_cbm ? number_format($shipment->volume_cbm, 2).' cbm' : '-' }}</dd></div>
                    <div><dt class="text-gray-500">Expected departure</dt><dd>{{ $shipment->expected_departure->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Expected arrival</dt><dd>{{ $shipment->expected_arrival->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Estimated arrival (ETA)</dt><dd>{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y H:i') : '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">Items</h3>
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @forelse ($shipment->items as $item)
                        <li>{{ $item->description }} &times; {{ $item->quantity }}</li>
                    @empty
                        <li class="list-none text-gray-500">No items.</li>
                    @endforelse
                </ul>
            </div>

            <a href="{{ route('shipments.index') }}" class="text-indigo-600 hover:underline text-sm">&larr; Back to shipments</a>
        </div>
    </div>
</x-app-layout>