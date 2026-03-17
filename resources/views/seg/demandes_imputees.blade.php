@extends('layouts.app')

@section('title', 'Demandes imputées (SEG)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes imputées (SEG)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) imputée(s)</p>
    </div>

    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[1000px]">
                <thead>
                    <tr>
                        <th class="min-w-[120px]">N° Demande</th>
                        <th class="min-w-[180px]">Demandeur</th>
                        <th class="min-w-[200px]">Objet</th>
                        <th class="min-w-[130px]">Nature</th>
                        <th class="min-w-[100px]">Site</th>
                        <th class="min-w-[120px]">Unité destination</th>
                        <th class="min-w-[100px]">Statut</th>
                        <th class="min-w-[100px]">Date</th>
                        <th class="text-right min-w-[100px]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr>
                        <td>
                            <span class="font-mono text-sm font-semibold text-senelec-purple">{{ $demande->numero_demande }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                    {{ $demande->user ? strtoupper(substr($demande->user->name ?? '', 0, 2)) : '?' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $demande->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $demande->user->service ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td><p class="text-sm text-gray-900 line-clamp-2">{{ $demande->objet ?? '-' }}</p></td>
                        <td><span class="text-sm text-gray-700">{{ $demande->nature ?? '-' }}</span></td>
                        <td><span class="text-sm text-gray-700">{{ $demande->site->libelle ?? '-' }}</span></td>
                        <td>
                            @if($demande->unite_destination)
                                <span class="badge-info">{{ $demande->unite_destination }}</span>
                                @if($demande->unite_destination_nom)
                                    <p class="text-xs text-gray-500 mt-1">{{ $demande->unite_destination_nom }}</p>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                        <td><span class="text-sm text-gray-500">{{ $demande->created_at?->format('d/m/Y') }}</span></td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('demande.show', $demande) }}" class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Aucune demande imputée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
