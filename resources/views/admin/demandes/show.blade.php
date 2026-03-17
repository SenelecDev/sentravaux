@extends('layouts.app')

@section('title', 'Demande #' . $demande->numero_demande)

@section('content')
<div class="space-y-6">
    <!-- Retour + Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.demandes.index') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Demande #{{ $demande->numero_demande }}</h1>
            <p class="mt-1 text-gray-500">{{ $demande->objet }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="card-senelec p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations générales</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-500">N° Demande</dt>
                    <dd class="font-medium text-gray-900">{{ $demande->numero_demande }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Objet</dt>
                    <dd class="font-medium text-gray-900">{{ $demande->objet }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Nature</dt>
                    <dd>
                        @if($demande->nature)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                {{ ucfirst($demande->nature) }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        @php
                            $statusColors = [
                                'brouillon' => 'bg-gray-100 text-gray-700',
                                'soumise' => 'bg-yellow-100 text-yellow-700',
                                'approuvée' => 'bg-green-100 text-green-700',
                                'rejetée' => 'bg-red-100 text-red-700',
                                'en_cours' => 'bg-blue-100 text-blue-700',
                                'terminée' => 'bg-emerald-100 text-emerald-700',
                                'clôturée' => 'bg-purple-100 text-purple-700',
                            ];
                            $color = $statusColors[$demande->statut] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $color }}">
                            {{ ucfirst(str_replace('_', ' ', $demande->statut ?? 'N/A')) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date de création</dt>
                    <dd class="font-medium text-gray-900">{{ $demande->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
                @if($demande->date_intervention)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date d'intervention</dt>
                    <dd class="font-medium text-gray-900">{{ $demande->date_intervention?->format('d/m/Y') }}</dd>
                </div>
                @endif
                @if($demande->observation)
                <div>
                    <dt class="text-gray-500 mb-1">Observation</dt>
                    <dd class="font-medium text-gray-900 bg-gray-50 p-3 rounded-lg text-sm">{{ $demande->observation }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Demandeur -->
        <div class="card-senelec p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Demandeur</h2>
            @if($demande->user)
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center text-lg font-bold text-purple-700">
                        {{ strtoupper(substr($demande->user->prenom ?? '', 0, 1) . substr($demande->user->nom ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $demande->user->prenom }} {{ $demande->user->nom }}</div>
                        <div class="text-sm text-gray-500">{{ $demande->user->matricule }}</div>
                    </div>
                </div>
                <dl class="space-y-2 text-sm">
                    @if($demande->user->email)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $demande->user->email }}</dd>
                    </div>
                    @endif
                    @if($demande->user->service)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Service</dt>
                        <dd class="text-gray-900">{{ $demande->user->service }}</dd>
                    </div>
                    @endif
                    @if($demande->user->direction)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Direction</dt>
                        <dd class="text-gray-900">{{ $demande->user->direction }}</dd>
                    </div>
                    @endif
                </dl>
            @else
                <p class="text-gray-400">Utilisateur non trouvé</p>
            @endif
        </div>
    </div>
</div>
@endsection
