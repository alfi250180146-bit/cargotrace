@php
    $input = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $location->exists ? __('Edit location') : __('New location') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST"
                      action="{{ $location->exists ? route('locations.update', $location) : route('locations.store') }}"
                      class="space-y-5">
                    @csrf
                    @if ($location->exists)
                        @method('PUT')
                    @endif

                    <div>
                        <x-input-label for="code" value="Code (max 10 characters, unique)" />
                        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                            :value="old('code', $location->code)" required />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $location->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="country" value="Country" />
                        <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                            :value="old('country', $location->country)" required />
                        <x-input-error :messages="$errors->get('country')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" value="Type" />
                        <select id="type" name="type" class="{{ $input }}" required>
                            @foreach (['seaport', 'airport', 'warehouse'] as $type)
                                <option value="{{ $type }}" @selected(old('type', $location->type ?: 'seaport') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="latitude" value="Latitude (-90 to 90)" />
                            <x-text-input id="latitude" name="latitude" type="number" step="any" class="mt-1 block w-full"
                                :value="old('latitude', $location->latitude)" required />
                            <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="longitude" value="Longitude (-180 to 180)" />
                            <x-text-input id="longitude" name="longitude" type="number" step="any" class="mt-1 block w-full"
                                :value="old('longitude', $location->longitude)" required />
                            <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Save</x-primary-button>
                        <a href="{{ route('locations.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>