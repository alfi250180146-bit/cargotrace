@php
    $input = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $currentLocation = $shipment->trackingEvents
        ->where('is_exception', false)
        ->whereNotNull('location_id')
        ->last()?->location;
    $sevColor = [
        'LOW' => 'bg-yellow-100 text-yellow-800',
        'MEDIUM' => 'bg-orange-100 text-orange-800',
        'HIGH' => 'bg-red-100 text-red-800',
    ];
    $statusColor = [
        'OPEN' => 'bg-red-100 text-red-800',
        'INVESTIGATING' => 'bg-blue-100 text-blue-800',
        'RESOLVED' => 'bg-green-100 text-green-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shipment') }} {{ $shipment->shipment_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 rounded-md bg-green-100 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Status</dt><dd class="font-semibold">{{ $shipment->current_status }}</dd></div>
                    <div>
                        <dt class="text-gray-500">Current location</dt>
                        <dd>{{ $currentLocation ? $currentLocation->name.', '.$currentLocation->country : '-' }}</dd>
                    </div>
                    <div><dt class="text-gray-500">Customer</dt><dd>{{ $shipment->customer->name }}</dd></div>
                    <div><dt class="text-gray-500">Carrier</dt><dd>{{ $shipment->carrier->name }} ({{ $shipment->carrier->code }})</dd></div>
                    <div><dt class="text-gray-500">Cargo type</dt><dd>{{ $shipment->cargo_type }}</dd></div>
                    <div><dt class="text-gray-500">Weight</dt><dd>{{ number_format($shipment->weight_kg, 2) }} kg</dd></div>
                    <div><dt class="text-gray-500">Origin</dt><dd>{{ $shipment->origin->name }}, {{ $shipment->origin->country }}</dd></div>
                    <div><dt class="text-gray-500">Destination</dt><dd>{{ $shipment->destination->name }}, {{ $shipment->destination->country }}</dd></div>
                    <div><dt class="text-gray-500">Volume</dt><dd>{{ $shipment->volume_cbm ? number_format($shipment->volume_cbm, 2).' cbm' : '-' }}</dd></div>
                    <div><dt class="text-gray-500">Expected departure</dt><dd>{{ $shipment->expected_departure->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Expected arrival</dt><dd>{{ $shipment->expected_arrival->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Estimated arrival (ETA)</dt><dd>{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y H:i') : '-' }}</dd></div>
                </dl>

                <div class="mt-6 flex items-center gap-3">
                    @can('update', $shipment)
                        <a href="{{ route('shipments.edit', $shipment) }}"
                           class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                            Edit
                        </a>
                    @endcan

                    @can('delete', $shipment)
                        <form method="POST" action="{{ route('shipments.destroy', $shipment) }}"
                              onsubmit="return confirm('Delete shipment {{ $shipment->shipment_number }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-3">ETA &amp; delay analysis</h3>

                <div class="flex flex-wrap items-center gap-2 mb-3">
                    @if ($eta['is_delayed'])
                        <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">DELAYED</span>
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $sevColor[$eta['severity']] }}">
                            Severity {{ $eta['severity'] }}
                        </span>
                        <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                            +{{ number_format($eta['delay_hours'], 1) }} h
                        </span>
                        @if ($eta['over_threshold'])
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-800 text-white">Over threshold</span>
                        @endif
                    @else
                        <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">ON TIME</span>
                    @endif
                </div>

                <p class="text-sm text-gray-700">{{ $eta['reason'] }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-4">Exceptions</h3>

                <x-input-error :messages="$errors->get('new_status')" class="mb-3" />

                <div class="space-y-4">
                    @forelse ($shipment->exceptions as $issue)
                        <div class="border rounded-md p-4 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold">{{ str_replace('_', ' ', $issue->type) }}</span>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $sevColor[$issue->severity] }}">{{ $issue->severity }}</span>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $statusColor[$issue->status] }}">{{ $issue->status }}</span>
                            </div>

                            <div class="text-xs text-gray-500 mt-2">
                                Detected {{ $issue->detected_at->format('d M Y H:i') }}
                                @if ($issue->resolved_at)
                                    &middot; Resolved {{ $issue->resolved_at->format('d M Y H:i') }}
                                @endif
                            </div>

                            @if ($issue->notes)
                                <div class="mt-2 text-gray-700">{{ $issue->notes }}</div>
                            @endif

                            @can('manageExceptions', $shipment)
                                @if (count($exceptionTransitions[$issue->status] ?? []) > 0)
                                    <form method="POST" action="{{ route('exceptions.update', $issue) }}" class="mt-3 flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="new_status" class="border-gray-300 rounded-md shadow-sm text-sm">
                                            @foreach ($exceptionTransitions[$issue->status] as $next)
                                                <option value="{{ $next }}">{{ $next }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-3 py-1 bg-gray-800 text-white text-xs rounded-md hover:bg-gray-700">
                                            Update status
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No exceptions recorded.</p>
                    @endforelse
                </div>

                @can('manageExceptions', $shipment)
                    <form method="POST" action="{{ route('shipments.exceptions.store', $shipment) }}" class="mt-6 pt-6 border-t space-y-4">
                        @csrf
                        <h4 class="font-semibold text-sm">Report an exception</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="type" value="Type" />
                                <select id="type" name="type" class="{{ $input }}" required>
                                    <option value="">-- Select type --</option>
                                    @foreach ($exceptionTypes as $type)
                                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ str_replace('_', ' ', $type) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="severity" value="Severity" />
                                <select id="severity" name="severity" class="{{ $input }}" required>
                                    @foreach ($severities as $severity)
                                        <option value="{{ $severity }}" @selected(old('severity') === $severity)>{{ $severity }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('severity')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="notes" value="Notes (optional)" />
                            <textarea id="notes" name="notes" rows="2" class="{{ $input }}">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <x-primary-button>Report exception</x-primary-button>
                    </form>
                @endcan
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold mb-4">Tracking timeline</h3>

                <ol class="relative border-l border-gray-200 ml-2 space-y-6">
                    @forelse ($shipment->trackingEvents as $event)
                        <li class="ml-5">
                            <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full {{ $event->is_exception ? 'bg-red-500' : 'bg-indigo-500' }}"></span>

                            <div class="text-xs text-gray-500">{{ $event->occurred_at->format('d M Y H:i') }}</div>

                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $event->is_exception ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $event->is_exception ? 'EXCEPTION' : $event->status }}
                                </span>
                                <span class="text-xs text-gray-500">{{ str_replace('_', ' ', $event->event_type) }}</span>
                            </div>

                            @if ($event->location)
                                <div class="text-sm mt-1">{{ $event->location->name }}, {{ $event->location->country }}</div>
                            @endif

                            @if ($event->description)
                                <div class="text-sm text-gray-700 mt-1">{{ $event->description }}</div>
                            @endif

                            <div class="text-xs text-gray-400 mt-1">by {{ $event->creator->name }}</div>
                        </li>
                    @empty
                        <li class="ml-5 text-sm text-gray-500">No tracking events yet.</li>
                    @endforelse
                </ol>
            </div>

            @can('addTracking', $shipment)
                @if (count($nextStatuses) > 0)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-semibold mb-1">Add tracking event</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Current status: {{ $shipment->current_status }}.
                            Next valid status: {{ implode(', ', $nextStatuses) }}.
                        </p>

                        <form method="POST" action="{{ route('shipments.tracking.store', $shipment) }}" class="space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="event_type" value="Event type" />
                                <select id="event_type" name="event_type" class="{{ $input }}" required>
                                    <option value="STATUS_CHANGE" @selected(old('event_type') === 'STATUS_CHANGE')>Status change</option>
                                    <option value="CHECKPOINT" @selected(old('event_type') === 'CHECKPOINT')>Checkpoint (location update)</option>
                                    <option value="EXCEPTION" @selected(old('event_type') === 'EXCEPTION')>Exception / issue</option>
                                </select>
                                <x-input-error :messages="$errors->get('event_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="status" value="New status (only used for Status change)" />
                                <select id="status" name="status" class="{{ $input }}">
                                    <option value="">-- Select status --</option>
                                    @foreach ($allStatuses as $status)
                                        <option value="{{ $status }}" @selected(old('status') === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="occurred_at" value="Occurred at" />
                                <x-text-input id="occurred_at" name="occurred_at" type="datetime-local" class="mt-1 block w-full"
                                    :value="old('occurred_at', now()->format('Y-m-d\TH:i'))" required />
                                <x-input-error :messages="$errors->get('occurred_at')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="location_id" value="Location (optional)" />
                                <select id="location_id" name="location_id" class="{{ $input }}">
                                    <option value="">-- No location --</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" @selected((string) old('location_id') === (string) $location->id)>
                                            {{ $location->name }}, {{ $location->country }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="description" value="Description (required for Exception)" />
                                <textarea id="description" name="description" rows="3" class="{{ $input }}">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <x-primary-button>Add event</x-primary-button>
                        </form>
                    </div>
                @else
                    <div class="p-4 rounded-md bg-gray-100 text-gray-700 text-sm">
                        This shipment is {{ $shipment->current_status }}. No more events can be added.
                    </div>
                @endif
            @endcan

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