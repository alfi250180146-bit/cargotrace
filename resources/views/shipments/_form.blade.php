@php
    $fmt = fn ($date) => $date ? $date->format('Y-m-d\TH:i') : '';
    $input = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="{{ $input }}" required>
            <option value="">-- Select customer --</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $shipment->customer_id) == $customer->id)>
                    {{ $customer->name }} ({{ $customer->email }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="carrier_id" value="Carrier" />
        <select id="carrier_id" name="carrier_id" class="{{ $input }}" required>
            <option value="">-- Select carrier --</option>
            @foreach ($carriers as $carrier)
                <option value="{{ $carrier->id }}" @selected(old('carrier_id', $shipment->carrier_id) == $carrier->id)>
                    {{ $carrier->name }} ({{ $carrier->code }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('carrier_id')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="origin_id" value="Origin" />
            <select id="origin_id" name="origin_id" class="{{ $input }}" required>
                <option value="">-- Select origin --</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(old('origin_id', $shipment->origin_id) == $location->id)>
                        {{ $location->name }}, {{ $location->country }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('origin_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="destination_id" value="Destination" />
            <select id="destination_id" name="destination_id" class="{{ $input }}" required>
                <option value="">-- Select destination --</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(old('destination_id', $shipment->destination_id) == $location->id)>
                        {{ $location->name }}, {{ $location->country }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('destination_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="cargo_type" value="Cargo type" />
        <x-text-input id="cargo_type" name="cargo_type" type="text" class="mt-1 block w-full"
            :value="old('cargo_type', $shipment->cargo_type)" required />
        <x-input-error :messages="$errors->get('cargo_type')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="weight_kg" value="Weight (kg)" />
            <x-text-input id="weight_kg" name="weight_kg" type="number" step="0.01" class="mt-1 block w-full"
                :value="old('weight_kg', $shipment->weight_kg)" required />
            <x-input-error :messages="$errors->get('weight_kg')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="volume_cbm" value="Volume (cbm, optional)" />
            <x-text-input id="volume_cbm" name="volume_cbm" type="number" step="0.01" class="mt-1 block w-full"
                :value="old('volume_cbm', $shipment->volume_cbm)" />
            <x-input-error :messages="$errors->get('volume_cbm')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="expected_departure" value="Expected departure" />
            <x-text-input id="expected_departure" name="expected_departure" type="datetime-local" class="mt-1 block w-full"
                :value="old('expected_departure', $fmt($shipment->expected_departure))" required />
            <x-input-error :messages="$errors->get('expected_departure')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="expected_arrival" value="Expected arrival" />
            <x-text-input id="expected_arrival" name="expected_arrival" type="datetime-local" class="mt-1 block w-full"
                :value="old('expected_arrival', $fmt($shipment->expected_arrival))" required />
            <x-input-error :messages="$errors->get('expected_arrival')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="estimated_arrival" value="Estimated arrival / ETA (optional, default = expected arrival)" />
        <x-text-input id="estimated_arrival" name="estimated_arrival" type="datetime-local" class="mt-1 block w-full"
            :value="old('estimated_arrival', $fmt($shipment->estimated_arrival))" />
        <x-input-error :messages="$errors->get('estimated_arrival')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ $submit }}</x-primary-button>
        <a href="{{ route('shipments.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
    </div>
</form>