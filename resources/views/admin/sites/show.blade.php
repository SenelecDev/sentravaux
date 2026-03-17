@extends('layouts.app')

@section('title', 'Site : ' . $site->libelle)

@section('content')
<div class="space-y-6">
    <!-- Retour + Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.sites.index') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $site->libelle }}</h1>
            <p class="mt-1 text-gray-500">
                {{ $site->code ? 'Code : ' . $site->code . ' — ' : '' }}
                {{ $site->ville ?? '' }} {{ $site->region ? '(' . $site->region . ')' : '' }}
                — {{ $site->demandes_count }} demande(s)
            </p>
        </div>
    </div>

    <!-- Infos -->
    <div class="card-senelec p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations du site</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">Code</span>
                <p class="font-medium text-gray-900">{{ $site->code ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Ville</span>
                <p class="font-medium text-gray-900">{{ $site->ville ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Région</span>
                <p class="font-medium text-gray-900">{{ $site->region ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Adresse</span>
                <p class="font-medium text-gray-900">{{ $site->adresse ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Statut</span>
                <p>
                    @if($site->is_active)
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Actif</span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Inactif</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Oracle ID</span>
                <p class="font-medium text-gray-900">{{ $site->oracle_location_id ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Demandes -->
    <div class="card-senelec overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Demandes de ce site</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-senelec">
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Objet</th>
                        <th>Demandeur</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $demande->numero_demande }}</td>
                            <td class="max-w-xs truncate">{{ $demande->objet }}</td>
                            <td>
                                @if($demande->user)
                                    {{ $demande->user->prenom }} {{ $demande->user->nom }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                    {{ ucfirst(str_replace('_', ' ', $demande->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-sm text-gray-500">{{ $demande->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                Aucune demande pour ce site
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($demandes->hasPages())
    <div>
        {{ $demandes->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
