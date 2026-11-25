@php
    $show = fn ($value, $suffix = '') => $value === null ? '-' : $value.$suffix;

    $cards = [
        ['Total shipments', $stats['total'], ''],
        ['In transit', $stats['in_transit'], ''],
        ['Delivered', $stats['delivered'], ''],
        ['Delayed now', $stats['delayed_now'], ''],
        ['Open exceptions', $stats['open_exceptions'], ''],
    ];

    $kpis = [
        ['Delay rate', $stats['delay_rate'], '%'],
        ['Exception rate', $stats['exception_rate'], '%'],
        ['On-time delivery rate', $stats['on_time_rate'], '%'],
        ['Average delay', $stats['avg_delay'], ' h'],
        ['Avg exception resolution', $stats['avg_resolution'], ' h'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    Welcome, {{ auth()->user()->name }} ({{ auth()->user()->role }}).
                    @if (auth()->user()->hasRole('customer'))
                        Showing your own shipments only.
                    @endif
                </p>

                <div class="flex flex-wrap gap-4 text-sm">
                    <a href="{{ route('shipments.index') }}" class="text-indigo-600 hover:underline">Shipments</a>
                    @if (auth()->user()->hasRole('admin'))
                        <a href="{{ route('carriers.index') }}" class="text-indigo-600 hover:underline">Carriers</a>
                        <a href="{{ route('locations.index') }}" class="text-indigo-600 hover:underline">Locations</a>
                        <a href="{{ route('settings.edit') }}" class="text-indigo-600 hover:underline">Delay settings</a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($cards as [$label, $value, $suffix])
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <div class="text-xs text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-semibold mt-1">{{ $show($value, $suffix) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($kpis as [$label, $value, $suffix])
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-indigo-500">
                        <div class="text-xs text-gray-500">{{ $label }}</div>
                        <div class="text-2xl font-semibold mt-1">{{ $show($value, $suffix) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold mb-3">Shipments by status</h3>
                    <div class="relative h-64"><canvas id="statusChart"></canvas></div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold mb-3">Shipments by carrier</h3>
                    <div class="relative h-64"><canvas id="carrierChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        };

        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: @json($stats['status_labels']),
                datasets: [{ label: 'Shipments', data: @json($stats['status_values']), backgroundColor: '#6366f1' }],
            },
            options: chartOptions,
        });

        new Chart(document.getElementById('carrierChart'), {
            type: 'bar',
            data: {
                labels: @json($stats['carrier_labels']),
                datasets: [{ label: 'Shipments', data: @json($stats['carrier_values']), backgroundColor: '#0ea5e9' }],
            },
            options: chartOptions,
        });
    </script>
</x-app-layout>