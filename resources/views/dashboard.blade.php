@php
    $show = fn ($value, $suffix = '') => $value === null ? '-' : $value.$suffix;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Welcome back, {{ auth()->user()->name }}.
                    @if (auth()->user()->hasRole('customer'))
                        Showing your own shipments only.
                    @endif
                </p>
            </div>

            <a href="{{ route('shipments.index') }}"
               class="inline-block px-4 py-2 bg-brand-700 text-white text-sm rounded-md hover:bg-brand-800">
                View shipments
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <x-stat-card label="Total shipments" :value="$stats['total']" accent="brand" />
                <x-stat-card label="In transit" :value="$stats['in_transit']" accent="sky" />
                <x-stat-card label="Delivered" :value="$stats['delivered']" accent="green" />
                <x-stat-card label="Delayed now" :value="$stats['delayed_now']" accent="amber" />
                <x-stat-card label="Open exceptions" :value="$stats['open_exceptions']" accent="red" />
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <x-stat-card label="Delay rate" :value="$show($stats['delay_rate'], '%')" accent="slate" />
                <x-stat-card label="Exception rate" :value="$show($stats['exception_rate'], '%')" accent="slate" />
                <x-stat-card label="On-time delivery rate" :value="$show($stats['on_time_rate'], '%')" accent="slate" />
                <x-stat-card label="Average delay" :value="$show($stats['avg_delay'], ' h')" accent="slate" />
                <x-stat-card label="Avg exception resolution" :value="$show($stats['avg_resolution'], ' h')" accent="slate" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-md p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Shipments by status</h3>
                    <div class="relative h-64"><canvas id="statusChart"></canvas></div>
                </div>

                <div class="bg-white border border-gray-200 rounded-md p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Shipments by carrier</h3>
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
                datasets: [{ label: 'Shipments', data: @json($stats['status_values']), backgroundColor: '#234470', borderRadius: 2 }],
            },
            options: chartOptions,
        });

        new Chart(document.getElementById('carrierChart'), {
            type: 'bar',
            data: {
                labels: @json($stats['carrier_labels']),
                datasets: [{ label: 'Shipments', data: @json($stats['carrier_values']), backgroundColor: '#5d8bc0', borderRadius: 2 }],
            },
            options: chartOptions,
        });
    </script>
</x-app-layout>