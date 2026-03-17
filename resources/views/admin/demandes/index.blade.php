@extends('layouts.app')

@section('title', 'Gestion des demandes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des demandes</h1>
            <p class="mt-1 text-gray-500">{{ $demandes->total() }} demande(s) trouvée(s)</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card-senelec p-4">
        <form action="{{ route('admin.demandes.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher (n° demande, objet, demandeur, matricule)..." class="input-senelec">
            </div>
            <div class="w-48">
                <select name="statut" class="input-senelec">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $statut)
                        <option value="{{ $statut }}" {{ request('statut') == $statut ? 'selected' : '' }}>
                            {{ ucfirst($statut) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <select name="nature" class="input-senelec">
                    <option value="">Toutes les natures</option>
                    @foreach($natures as $nature)
                        <option value="{{ $nature }}" {{ request('nature') == $nature ? 'selected' : '' }}>
                            {{ ucfirst($nature) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-senelec">Filtrer</button>
            <a href="{{ route('admin.demandes.index') }}" class="btn-secondary px-4 flex items-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec">
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Objet</th>
                        <th>Demandeur</th>
                        <th>Nature</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $demande->numero_demande }}</td>
                            <td class="max-w-xs truncate">{{ $demande->objet }}</td>
                            <td>
                                @if($demande->user)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-xs font-bold text-purple-700">
                                            {{ strtoupper(substr($demande->user->prenom ?? '', 0, 1) . substr($demande->user->nom ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium">{{ $demande->user->prenom }} {{ $demande->user->nom }}</div>
                                            <div class="text-xs text-gray-500">{{ $demande->user->matricule }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($demande->nature)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                        {{ ucfirst($demande->nature) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
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
                            </td>
                            <td class="text-sm text-gray-500">{{ $demande->created_at?->format('d/m/Y') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.demandes.show', $demande) }}" 
                                   class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors inline-block" title="Voir la demande">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                Aucune demande trouvée
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
