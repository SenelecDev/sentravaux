@extends('layouts.app')

@section('title', 'Synchronisation Oracle')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 font-['Rajdhani']">
                Synchronisation <span class="text-senelec-purple">Oracle HR</span>
            </h1>
            <p class="text-gray-600">Synchroniser les utilisateurs avec la base de données Oracle</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-senelec-outline">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux utilisateurs
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card-senelec p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-senelec-purple/10 rounded-lg">
                    <svg class="w-6 h-6 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total utilisateurs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
            </div>
        </div>

        <div class="card-senelec p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Avec matricule</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['users_with_matricule'] }}</p>
                </div>
            </div>
        </div>

        <div class="card-senelec p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Synchronisés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['synced_users'] }}</p>
                </div>
            </div>
        </div>

        <div class="card-senelec p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Non synchronisés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['never_synced'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Synchronisation Oracle -->
        <div class="card-senelec p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sync Oracle HR
            </h2>
            <p class="text-gray-600 mb-4 text-sm">
                Met à jour nom, prénom, poste, service depuis Oracle HR. Exécution en arrière-plan.
            </p>
            <form action="{{ route('admin.users.sync-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn-senelec w-full" onclick="return confirm('Lancer la synchronisation Oracle en arrière-plan?')">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Oracle ({{ $stats['users_with_matricule'] }})
                </button>
            </form>
        </div>

        <!-- Synchronisation LDAP -->
        <div class="card-senelec p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Sync LDAP
            </h2>
            <p class="text-gray-600 mb-4 text-sm">
                Met à jour téléphone, email, photo de profil depuis l'annuaire LDAP. Exécution en arrière-plan.
            </p>
            <form action="{{ route('admin.users.sync-ldap') }}" method="POST">
                @csrf
                <button type="submit" class="btn-senelec-outline w-full" onclick="return confirm('Lancer la synchronisation LDAP en arrière-plan?')">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                    LDAP ({{ $stats['users_with_matricule'] }})
                </button>
            </form>
        </div>

        <!-- Synchronisation Photos seulement -->
        <div class="card-senelec p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Photos LDAP
            </h2>
            <p class="text-gray-600 mb-4 text-sm">
                Synchronise uniquement les photos de profil. Plus rapide, utilisateurs sans photo seulement.
            </p>
            <form action="{{ route('admin.users.sync-photos') }}" method="POST">
                @csrf
                <button type="submit" class="btn-success w-full" onclick="return confirm('Lancer la synchronisation des photos en arrière-plan?')">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                    </svg>
                    Photos seulement
                </button>
            </form>
        </div>
    </div>

    <!-- Importation massive Oracle + LDAP -->
    <div class="card-senelec p-6 border-2 border-senelec-orange/30" style="background: linear-gradient(135deg, rgba(232, 93, 4, 0.05) 0%, rgba(179, 0, 108, 0.05) 100%);">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-senelec-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    🚀 Importation Massive Oracle + LDAP
                </h2>
                <p class="text-gray-600 mb-4">
                    Importer <strong>TOUS</strong> les employés actifs depuis Oracle HR avec complétion via LDAP.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-white/50 rounded-lg p-3">
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium text-gray-700">Matricule: Oracle</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Source la plus fiable</p>
                    </div>
                    <div class="bg-white/50 rounded-lg p-3">
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="font-medium text-gray-700">Champs manquants: LDAP</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Email, téléphone, etc.</p>
                    </div>
                    <div class="bg-white/50 rounded-lg p-3">
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="font-medium text-gray-700">Photos: LDAP</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Photos de profil</p>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.users.import-all') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="btn-senelec w-full text-lg py-3" onclick="return confirm('⚠️ ATTENTION: Cette opération va importer TOUS les employés Oracle et peut prendre plusieurs minutes.\n\nContinuer?')">
                <svg class="w-6 h-6 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                🚀 Lancer l'Importation Massive
            </button>
        </form>
        <p class="text-xs text-gray-500 mt-3 text-center">
            ⏱️ Cette opération s'exécute en arrière-plan. Consultez les logs pour suivre la progression.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Importer un nouvel utilisateur -->
        <div class="card-senelec p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-senelec-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Importer un utilisateur
            </h2>
            <p class="text-gray-600 mb-4">
                Créer un nouvel utilisateur à partir de son matricule Oracle.
            </p>
            <form action="{{ route('admin.users.import') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="matricule" class="label">Matricule</label>
                    <input type="text" name="matricule" id="matricule" class="input-senelec w-full" placeholder="Ex: 12345" required>
                </div>
                <button type="submit" class="btn-senelec-outline w-full">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Importer depuis Oracle
                </button>
            </form>
        </div>
    </div>

    <!-- Utilisateurs récemment synchronisés -->
    <div class="card-senelec p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Dernières synchronisations
        </h2>

        @if($recentlySynced->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matricule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Synchronisé le</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentlySynced as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-senelec-purple/10 flex items-center justify-center">
                                                <span class="text-senelec-purple font-semibold">{{ $user->initials }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->matricule }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->service ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->oracle_synced_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('admin.users.sync', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-senelec-purple hover:text-senelec-purple/80">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Resync
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Aucune synchronisation récente</p>
            </div>
        @endif
    </div>

    <!-- Logs en temps réel -->
    <div class="card-senelec p-6" x-data="syncLogs()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                📡 Logs en Temps Réel
                <span x-show="status.running" class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 mr-1 bg-green-500 rounded-full animate-pulse"></span>
                    En cours...
                </span>
            </h2>
            <div class="flex items-center gap-2">
                <button @click="toggleAutoRefresh()" class="text-sm px-3 py-1 rounded-lg" :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                    <span x-text="autoRefresh ? '⏸️ Pause' : '▶️ Auto'"></span>
                </button>
                <button @click="clearLogs()" class="text-sm px-3 py-1 rounded-lg bg-red-100 text-red-700 hover:bg-red-200">
                    🗑️ Effacer
                </button>
            </div>
        </div>
        
        <!-- Barre de progression -->
        <div x-show="status.running && status.total > 0" class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span x-text="status.operation"></span>
                <span x-text="status.progress + ' / ' + status.total"></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-senelec-purple h-2.5 rounded-full transition-all duration-300" :style="'width: ' + (status.total > 0 ? (status.progress / status.total * 100) : 0) + '%'"></div>
            </div>
        </div>

        <!-- Console de logs -->
        <div class="bg-gray-900 rounded-lg p-4 h-64 overflow-y-auto font-mono text-sm" id="log-console">
            <template x-if="logs.length === 0">
                <p class="text-gray-500 text-center">Aucun log. Lancez une synchronisation pour voir les logs en temps réel.</p>
            </template>
            <template x-for="log in logs" :key="log.time + log.message">
                <div class="flex items-start gap-2 py-0.5">
                    <span class="text-gray-500 flex-shrink-0" x-text="'[' + log.time + ']'"></span>
                    <span :class="{
                        'text-green-400': log.type === 'success' || log.type === 'start',
                        'text-yellow-400': log.type === 'warning',
                        'text-red-400': log.type === 'error',
                        'text-blue-400': log.type === 'info',
                        'text-gray-300': !['success', 'warning', 'error', 'info', 'start'].includes(log.type)
                    }" x-text="log.message"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Commandes artisan -->
    <div class="card-senelec p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Commandes artisan disponibles
        </h2>
        <div class="bg-gray-900 rounded-lg p-4 text-sm font-mono text-green-400 space-y-2">
            <p class="text-gray-400"># 🚀 Importer TOUS les employés (Oracle + LDAP)</p>
            <p>php artisan users:sync-oracle --import-all</p>
            <p class="text-gray-400 mt-3"># Synchroniser un utilisateur spécifique</p>
            <p>php artisan users:sync-oracle --matricule=12345</p>
            <p class="text-gray-400 mt-3"># Synchroniser tous les utilisateurs existants</p>
            <p>php artisan users:sync-oracle --all</p>
            <p class="text-gray-400 mt-3"># Importer de nouveaux utilisateurs depuis Oracle</p>
            <p>php artisan users:sync-oracle --import --limit=100</p>
            <p class="text-gray-400 mt-3"># Synchroniser les photos LDAP seulement</p>
            <p>php artisan users:sync-oracle --photos</p>
            <p class="text-gray-400 mt-3"># Mode simulation (sans modifications)</p>
            <p>php artisan users:sync-oracle --import-all --dry-run</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function syncLogs() {
    return {
        logs: [],
        status: {
            running: false,
            operation: null,
            progress: 0,
            total: 0
        },
        autoRefresh: true,
        interval: null,

        init() {
            this.fetchLogs();
            this.startAutoRefresh();
        },

        async fetchLogs() {
            try {
                const response = await fetch('{{ route("admin.users.sync-logs") }}');
                const data = await response.json();
                this.logs = data.logs || [];
                this.status = data.status || { running: false };
                
                // Auto-scroll vers le bas
                this.$nextTick(() => {
                    const console = document.getElementById('log-console');
                    if (console) {
                        console.scrollTop = console.scrollHeight;
                    }
                });
            } catch (e) {
                console.error('Erreur fetch logs:', e);
            }
        },

        startAutoRefresh() {
            this.interval = setInterval(() => {
                if (this.autoRefresh) {
                    this.fetchLogs();
                }
            }, 2000);
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
        },

        async clearLogs() {
            try {
                await fetch('{{ route("admin.users.sync-logs.clear") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
                this.logs = [];
                this.status = { running: false };
            } catch (e) {
                console.error('Erreur clear logs:', e);
            }
        },

        destroy() {
            if (this.interval) {
                clearInterval(this.interval);
            }
        }
    }
}
</script>
@endpush
@endsection
