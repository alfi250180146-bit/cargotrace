@php
    $kindLabel = ['status' => 'Status', 'delay' => 'Delay', 'exception' => 'Exception'];
    $kindClass = [
        'status' => 'bg-teal-50 text-teal-800 border-teal-200',
        'delay' => 'bg-amber-50 text-amber-800 border-amber-200',
        'exception' => 'bg-red-50 text-red-800 border-red-200',
    ];
    $hasUnread = auth()->user()->unreadNotifications()->exists();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Notifications</h2>

            @if ($hasUnread)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 rounded-md bg-green-50 text-green-800 text-sm border border-green-200">{{ session('status') }}</div>
            @endif

            <div class="bg-white border border-gray-200 rounded-md divide-y divide-gray-100">
                @forelse ($notifications as $notification)
                    @php $kind = $notification->data['kind'] ?? 'status'; @endphp
                    <a href="{{ route('notifications.show', $notification->id) }}"
                       class="flex items-start gap-3 px-5 py-4 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-brand-50' }}">
                        <span class="mt-0.5 inline-flex shrink-0 items-center rounded border px-2 py-0.5 text-xs font-semibold uppercase tracking-wide {{ $kindClass[$kind] ?? $kindClass['status'] }}">
                            {{ $kindLabel[$kind] ?? $kind }}
                        </span>

                        <span class="flex-1">
                            <span class="block text-sm {{ $notification->read_at ? 'text-gray-700' : 'font-semibold text-gray-900' }}">
                                {{ $notification->data['message'] ?? '' }}
                            </span>
                            <span class="block text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>

                        @unless ($notification->read_at)
                            <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-brand-700"></span>
                        @endunless
                    </a>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">No notifications yet.</div>
                @endforelse
            </div>

            <div>{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>