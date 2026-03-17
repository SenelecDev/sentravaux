@extends('layouts.app')

@section('title', $user->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h1>
                <p class="mt-0.5 text-sm text-gray-500 font-mono">{{ $user->matricule }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.impersonate.start', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5" style="background-color: #2B1444; box-shadow: 0 4px 15px rgba(43,20,68,0.3);" 
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Simuler
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-senelec inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <!-- 3 colonnes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Colonne 1 : Profil -->
        <div class="card-senelec p-6">
            <div class="flex flex-col items-center text-center">
                @if($user->photo_url)
                    <img src="{{ $user->photo_url }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-gray-100" alt="{{ $user->full_name }}">
                @else
                    <div class="h-24 w-24 rounded-full flex items-center justify-center text-white text-2xl font-bold ring-4 ring-gray-100" style="background-color: #2B1444;">
                        {{ $user->initials }}
                    </div>
                @endif
                <h2 class="mt-4 text-lg font-bold text-gray-900">{{ $user->prenom }} {{ $user->nom }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->poste ?? $user->fonction_oracle ?? '-' }}</p>
                <div class="mt-2">
                    @if($user->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Actif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Inactif</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 border-t border-gray-100 pt-4 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Email</span>
                    <span class="text-gray-900 font-medium text-right truncate ml-2 max-w-[200px]">{{ $user->email ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Téléphone</span>
                    <span class="text-gray-900 font-medium">{{ $user->telephone ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Matricule</span>
                    <span class="text-gray-900 font-mono font-semibold">{{ $user->matricule ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Source</span>
                    <span>
                        @if($user->oracle_person_id)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Oracle</span>
                        @elseif($user->ldap_guid)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">LDAP</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Local</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Colonne 2 : Infos professionnelles -->
        <div class="card-senelec p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg bg-purple-100">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Infos professionnelles</h3>
            </div>

            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Direction</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $user->direction ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Département</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $user->departement ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Service</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $user->service ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Délégation</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $user->delegation ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Localisation</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $user->localisation ?? '-' }}</dd>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Rôles</dt>
                    <dd class="mt-2">
                        @if($user->roles->count())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($user->roles as $role)
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-sm text-gray-400">Aucun rôle attribué</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Colonne 3 : Statistiques -->
        <div class="card-senelec p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg bg-orange-100">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Statistiques</h3>
            </div>

            <!-- Compteurs -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ $user->demandes_count ?? 0 }}</div>
                    <div class="text-xs text-blue-600 mt-1">Demandes créées</div>
                </div>
                <div class="rounded-xl border border-green-100 bg-green-50/50 p-4 text-center">
                    <div class="text-2xl font-bold text-green-700">{{ $user->demandes()->where('statut', 'terminée')->count() }}</div>
                    <div class="text-xs text-green-600 mt-1">Demandes terminées</div>
                </div>
                <div class="rounded-xl border border-yellow-100 bg-yellow-50/50 p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-700">{{ $user->demandes()->where('statut', 'en_cours')->count() }}</div>
                    <div class="text-xs text-yellow-600 mt-1">En cours</div>
                </div>
                <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-4 text-center">
                    <div class="text-2xl font-bold text-purple-700">{{ $user->demandes()->where('statut', 'clôturée')->count() }}</div>
                    <div class="text-xs text-purple-600 mt-1">Clôturées</div>
                </div>
            </div>

            <!-- Dates -->
            <div class="border-t border-gray-100 pt-4 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Date de création</span>
                    <span class="text-gray-900 font-medium">{{ $user->created_at?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Dernière modification</span>
                    <span class="text-gray-900 font-medium">{{ $user->updated_at?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Dernière activité</span>
                    <span class="text-gray-900 font-medium">{{ $user->last_activity_at?->format('d/m/Y H:i') ?? 'Jamais' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Synchro Oracle</span>
                    <span class="text-gray-900 font-medium">{{ $user->oracle_synced_at?->format('d/m/Y H:i') ?? 'Jamais' }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
