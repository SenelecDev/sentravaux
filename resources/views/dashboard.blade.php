@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
            <p class="mt-1 text-sm text-gray-600">Bienvenue, {{ auth()->user()->prenom ?? auth()->user()->name }} !</p>
        </div>
    </div>

    <!-- Stat Cards -->
    @if(!empty($availableDashboards) && count($availableDashboards) > 1)
    <div class="card-senelec p-5">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Choisir un espace</h2>
        <p class="text-sm text-gray-500 mb-4">Ce compte possède plusieurs rôles. Ouvrez le tableau de bord souhaité.</p>
        @php
            $labels = [
                'admin' => 'Administration',
                'dage' => 'DAGE',
                'sad' => 'SA',
                'seg' => 'SEG',
                'sgb' => 'SGB',
                'approbateur' => 'Approbateur',
                'umt' => 'UMT',
                'ubt' => 'UBT',
                'unsp' => 'UNSP',
                'umr' => 'UMR',
                'utgc' => 'UTGC',
                'ual' => 'UAL',
                'ucc' => 'UCC',
                'equipe' => 'Équipe',
                'demandeur' => 'Demandeur',
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($availableDashboards as $role => $route)
                <a href="{{ route($route) }}" class="btn-secondary text-sm text-center">
                    {{ $labels[$role] ?? strtoupper($role) }}
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card-purple">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Exemple Stat 1</p>
                    <p class="stat-value">0</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-senelec-purple/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card-teal">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Exemple Stat 2</p>
                    <p class="stat-value">0</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-senelec-teal/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-senelec-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card-orange">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Exemple Stat 3</p>
                    <p class="stat-value">0</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-senelec-orange/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-senelec-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card-magenta">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Exemple Stat 4</p>
                    <p class="stat-value">0</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-senelec-magenta/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-senelec-magenta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Activité récente</h2>
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>Aucune activité récente</p>
            </div>
        </div>

        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="#" class="quick-action">
                    <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-sm text-gray-600">Nouvelle action</span>
                </a>
                <a href="#" class="quick-action">
                    <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-sm text-gray-600">Voir la liste</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
