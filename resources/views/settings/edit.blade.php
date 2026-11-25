<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Delay settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 rounded-md bg-green-100 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-6">
                    These values drive the delay rules. A shipment is delayed when its ETA is later than the expected arrival.
                    A carrier can override the default threshold in its own record.
                </p>

                <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="delay_threshold_hours" value="Default delay threshold (hours)" />
                        <x-text-input id="delay_threshold_hours" name="delay_threshold_hours" type="number" step="0.1" class="mt-1 block w-full"
                            :value="old('delay_threshold_hours', $values['delay_threshold_hours'])" required />
                        <x-input-error :messages="$errors->get('delay_threshold_hours')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="severity_low_max_hours" value="LOW severity up to (hours)" />
                        <x-text-input id="severity_low_max_hours" name="severity_low_max_hours" type="number" step="0.1" class="mt-1 block w-full"
                            :value="old('severity_low_max_hours', $values['severity_low_max_hours'])" required />
                        <x-input-error :messages="$errors->get('severity_low_max_hours')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="severity_medium_max_hours" value="MEDIUM severity up to (hours), above this is HIGH" />
                        <x-text-input id="severity_medium_max_hours" name="severity_medium_max_hours" type="number" step="0.1" class="mt-1 block w-full"
                            :value="old('severity_medium_max_hours', $values['severity_medium_max_hours'])" required />
                        <x-input-error :messages="$errors->get('severity_medium_max_hours')" class="mt-2" />
                    </div>

                    <x-primary-button>Save settings</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>