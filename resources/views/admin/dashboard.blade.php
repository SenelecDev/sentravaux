@extends('layouts.app')

@section('title', 'Dashboard Administration')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Administration</h1>
        <p class="mt-1 text-sm text-gray-600">Vue d'ensemble du système SENTRAVAUX</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card-gradient">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/70 text-sm">Utilisateurs</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['users_count'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card-teal">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Actifs</p>
                    <p class="stat-value">{{ $stats['active_users'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-senelec-teal/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-senelec-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick links admin -->
    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Gestion</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.users.index') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-sm text-gray-600">Utilisateurs</span>
            </a>
        </div>
    </div>
</div>
@endsection
