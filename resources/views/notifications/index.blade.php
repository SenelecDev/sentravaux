@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6" x-data="notificationsPage()">
    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="mt-1 text-gray-500">{{ $notifications->total() }} notification(s) au total</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="markAllAsRead()" class="btn-senelec-outline text-sm" :disabled="loading">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Tout marquer comme lu
            </button>
        </div>
    </div>

    {{-- Liste des notifications --}}
    <div class="card-senelec divide-y divide-gray-100">
        @forelse($notifications as $notification)
        <div class="flex items-start gap-4 p-5 hover:bg-gray-50 transition {{ !$notification->is_read ? 'bg-blue-50/40' : '' }}"
             id="notification-{{ $notification->id }}">
            {{-- Icône --}}
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full flex items-center justify-center
                    @switch($notification->color ?? 'blue')
                        @case('green') bg-green-100 text-green-600 @break
                        @case('red') bg-red-100 text-red-600 @break
                        @case('orange') bg-orange-100 text-orange-600 @break
                        @case('yellow') bg-yellow-100 text-yellow-600 @break
                        @case('purple') bg-purple-100 text-purple-600 @break
                        @case('teal') bg-teal-100 text-teal-600 @break
                        @default bg-blue-100 text-blue-600
                    @endswitch
                ">
                    @switch($notification->icon ?? 'bell')
                        @case('check')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @break
                        @case('x')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @break
                        @case('arrow-right')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            @break
                        @case('clipboard')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            @break
                        @case('exclamation')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            @break
                        @default
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @endswitch
                </div>
            </div>

            {{-- Contenu --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 {{ !$notification->is_read ? 'font-bold' : '' }}">
                            {{ $notification->title }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $notification->message }}</p>
                        @if($notification->demande)
                            <p class="text-xs text-gray-400 mt-1">
                                Demande : <span class="font-medium text-[#e30613]">{{ $notification->demande->numero_demande }}</span>
                            </p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">
                            <time datetime="{{ $notification->created_at->toISOString() }}" title="{{ $notification->created_at->format('d/m/Y H:i:s') }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @if(!$notification->is_read)
                        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0" title="Non lu"></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                @if($notification->url)
                <a href="{{ $notification->url }}" class="p-1.5 text-gray-400 hover:text-[#e30613] transition" title="Voir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                @endif

                @if(!$notification->is_read)
                <button @click="markAsRead({{ $notification->id }})" class="p-1.5 text-gray-400 hover:text-green-600 transition" title="Marquer comme lu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
                @endif

                <button @click="deleteNotification({{ $notification->id }})" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="Supprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="mt-4 text-gray-500">Aucune notification pour le moment.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="flex justify-center">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function notificationsPage() {
    return {
        loading: false,

        async markAsRead(id) {
            try {
                const response = await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const el = document.getElementById(`notification-${id}`);
                    if (el) {
                        el.classList.remove('bg-blue-50/40');
                        el.querySelector('.bg-blue-500')?.remove();
                    }
                }
            } catch (e) { console.error(e); }
        },

        async markAllAsRead() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("notifications.read-all") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                if (response.ok) location.reload();
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        async deleteNotification(id) {
            if (!confirm('Supprimer cette notification ?')) return;
            try {
                const response = await fetch(`/notifications/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const el = document.getElementById(`notification-${id}`);
                    if (el) el.remove();
                }
            } catch (e) { console.error(e); }
        }
    };
}
</script>
@endpush
