@php
    $field = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500';
    $roleClass = [
        'admin' => 'bg-brand-50 text-brand-800 border-brand-200',
        'operator' => 'bg-sky-50 text-sky-800 border-sky-200',
        'customer' => 'bg-slate-50 text-slate-700 border-slate-300',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Users</h2>
            <a href="{{ route('users.create') }}"
               class="inline-block px-4 py-2 bg-brand-700 text-white text-sm rounded-md hover:bg-brand-800">
                New user
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 rounded-md bg-green-50 text-green-800 text-sm border border-green-200">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-md bg-red-50 text-red-800 text-sm border border-red-200">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('users.index') }}" class="bg-white border border-gray-200 rounded-md p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="q" class="text-xs text-gray-600">Name or email</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="role" class="text-xs text-gray-600">Role</label>
                        <select id="role" name="role" class="{{ $field }}">
                            <option value="">All</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">Filter</button>
                    <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
                </div>
            </form>

            <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Created</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">
                                        {{ $row->name }}
                                        @if ($row->id === auth()->id())
                                            <span class="ms-1 text-xs text-gray-500">(you)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">{{ $row->email }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-semibold uppercase tracking-wide {{ $roleClass[$row->role] ?? $roleClass['customer'] }}">
                                            {{ $row->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">{{ $row->created_at?->format('d M Y') }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('users.edit', $row) }}" class="text-brand-700 hover:underline">Edit</a>

                                            @if ($row->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.destroy', $row) }}"
                                                      onsubmit="return confirm('Delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>