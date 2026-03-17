@extends('layouts.app')

@section('title', 'Rôles & Permissions')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rôles & Permissions</h1>
            <p class="mt-1 text-gray-500">{{ $roles->count() }} rôle(s) — {{ $permissions->count() }} permission(s) — {{ $totalUsers }} utilisateur(s) total</p>
        </div>
    </div>

    <!-- Rôles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($roles as $role)
            <a href="{{ route('admin.roles.show', $role) }}" class="card-senelec p-5 hover:shadow-lg transition-shadow group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm"
                         style="background-color: #2B1444;">
                        {{ strtoupper(substr($role->name, 0, 2)) }}
                    </div>
                    <svg class="w-5 h-5 text-gray-300 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $role->name }}</h3>
                <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $role->users_count }} utilisateur(s)
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ $role->permissions_count }} permission(s)
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Permissions -->
    <div class="card-senelec p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Toutes les permissions</h2>
        @if($permissions->count())
            <div class="flex flex-wrap gap-2">
                @foreach($permissions as $permission)
                    <span class="px-3 py-1.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                        {{ $permission->name }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucune permission définie.</p>
        @endif
    </div>
</div>
@endsection
