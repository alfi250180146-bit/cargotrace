@php
    $field = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500';

    $sortLink = function (string $col, string $label) use ($sort, $dir) {
        $nextDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
        $arrow = $sort === $col ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';
        $url = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir, 'page' => null]);

        return '<a href="'.e($url).'" class="hover:underline">'.e($label.$arrow).'</a>';
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shipments</h2>

            @can('create', App\Models\Shipment::class)
                <a href="{{ route('shipments.create') }}"
                   class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                    New shipment
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 rounded-lg bg-green-50 text-green-800 text-sm ring-1 ring-green-600/20">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-50 text-red-800 text-sm ring-1 ring-red-600/20">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('shipments.index') }}" class="bg-white shadow-sm rounded-xl p-6">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="dir" value="{{ $dir }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="q" class="text-xs text-gray-600">Shipment number</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="CT-2026-..." class="{{ $field }}">
                    </div>

                    <div>
                        <label for="status" class="text-xs text-gray-600">Status</label>
                        <select id="status" name="status" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="carrier_id" class="text-xs text-gray-600">Carrier</label>
                        <select id="carrier_id" name="carrier_id" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}" @selected((string) ($filters['carrier_id'] ?? '') === (string) $carrier->id)>{{ $carrier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="delay" class="text-xs text-gray-600">Delay (ETA later than expected)</label>
                        <select id="delay" name="delay" class="{{ $field }}">
                            <option value="">All</option>
                            <option value="delayed" @selected(($filters['delay'] ?? '') === 'delayed')>Delayed</option>
                            <option value="on_time" @selected(($filters['delay'] ?? '') === 'on_time')>On time</option>
                        </select>
                    </div>

                    <div>
                        <label for="origin_id" class="text-xs text-gray-600">Origin</label>
                        <select id="origin_id" name="origin_id" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected((string) ($filters['origin_id'] ?? '') === (string) $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="destination_id" class="text-xs text-gray-600">Destination</label>
                        <select id="destination_id" name="destination_id" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected((string) ($filters['destination_id'] ?? '') === (string) $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="text-xs text-gray-600">Expected arrival from</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="date_to" class="text-xs text-gray-600">Expected arrival to</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="{{ $field }}">
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">Filter</button>
                    <a href="{{ route('shipments.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
                </div>
            </form>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">{!! $sortLink('shipment_number', 'Shipment No') !!}</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Carrier</th>
                                <th class="px-6 py-3">Route</th>
                                <th class="px-6 py-3">{!! $sortLink('expected_arrival', 'Expected arrival') !!}</th>
                                <th class="px-6 py-3">{!! $sortLink('current_status', 'Status') !!}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($shipments as $shipment)
                                @php
                                    $late = $shipment->current_status !== 'DELIVERED'
                                        && $shipment->estimated_arrival
                                        && $shipment->estimated_arrival->gt($shipment->expected_arrival);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="font-medium text-indigo-600 hover:underline">
                                            {{ $shipment->shipment_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3">{{ $shipment->customer->name }}</td>
                                    <td class="px-6 py-3">{{ $shipment->carrier->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">{{ $shipment->origin->code }} &rarr; {{ $shipment->destination->code }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">{{ $shipment->expected_arrival->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <x-status-badge :status="$shipment->current_status" />
                                        @if ($late)
                                            <x-status-badge status="DELAYED" class="ms-1" />
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">No shipments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $shipments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>