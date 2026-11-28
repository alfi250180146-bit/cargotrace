@php
    $field = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500';
    $actionClass = [
        'created' => 'bg-green-50 text-green-800 border-green-200',
        'updated' => 'bg-sky-50 text-sky-800 border-sky-200',
        'deleted' => 'bg-red-50 text-red-800 border-red-200',
    ];
    $show = fn ($value) => $value === null ? '-' : (is_scalar($value) ? (string) $value : json_encode($value));
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">Audit log</h2>
        <p class="text-sm text-gray-500 mt-1">Who changed what, and when. Passwords are never recorded.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if ($errors->any())
                <div class="p-4 rounded-md bg-red-50 text-red-800 text-sm border border-red-200">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('audit-logs.index') }}" class="bg-white border border-gray-200 rounded-md p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="entity_type" class="text-xs text-gray-600">Entity</label>
                        <select id="entity_type" name="entity_type" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach ($entityTypes as $type)
                                <option value="{{ $type }}" @selected(($filters['entity_type'] ?? '') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="action" class="text-xs text-gray-600">Action</label>
                        <select id="action" name="action" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach (['created', 'updated', 'deleted'] as $action)
                                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
                </div>
            </form>

            <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Time</th>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3">Action</th>
                                <th class="px-6 py-3">Entity</th>
                                <th class="px-6 py-3">Changes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($logs as $log)
                                @php
                                    $old = $log->old_values ?? [];
                                    $new = $log->new_values ?? [];
                                    $fields = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
                                @endphp
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-6 py-3 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                    <td class="px-6 py-3">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-semibold uppercase tracking-wide {{ $actionClass[$log->action] ?? '' }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="font-medium text-gray-900">{{ $log->entity_type }} #{{ $log->entity_id }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->description }}</div>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if (count($fields) > 0)
                                            <details>
                                                <summary class="cursor-pointer text-xs text-brand-700">{{ count($fields) }} field(s)</summary>
                                                <table class="mt-2 text-xs">
                                                    @foreach ($fields as $name)
                                                        <tr>
                                                            <td class="pr-3 py-0.5 font-medium text-gray-700">{{ $name }}</td>
                                                            <td class="pr-2 text-gray-500">{{ $log->action === 'created' ? '' : $show($old[$name] ?? null) }}</td>
                                                            <td class="pr-2 text-gray-400">{{ $log->action === 'updated' ? '→' : '' }}</td>
                                                            <td class="text-gray-900">{{ $log->action === 'deleted' ? '' : $show($new[$name] ?? null) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </details>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No audit entries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>