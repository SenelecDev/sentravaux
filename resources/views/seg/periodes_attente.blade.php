@extends('layouts.app')

@section('title', 'Périodes en attente de validation')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Périodes en attente de validation</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) avec période en attente</p>
    </div>

    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[1000px]">
                <thead>
                    <tr>
                        <th class="min-w-[120px]">N° Demande</th>
                        <th class="min-w-[180px]">Demandeur</th>
                        <th class="min-w-[130px]">Nature</th>
                        <th class="min-w-[180px]">Période</th>
                        <th class="min-w-[100px]">Statut</th>
                        <th class="text-right min-w-[200px]">Actions</th>
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
                                @php
                                    $photoUrl = $demande->user->photo_url ?? null;
                                @endphp
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="{{ $demande->user->name ?? 'Demandeur' }}"
                                         class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                        {{ $demande->user ? strtoupper(substr($demande->user->name ?? '', 0, 2)) : '?' }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $demande->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $demande->user->service ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-sm text-gray-700">{{ $demande->nature ?? '-' }}</span></td>
                        <td>
                            <span class="text-sm text-gray-700">
                                {{ $demande->date_debut_intervention ? $demande->date_debut_intervention->format('d/m/Y H:i') : '?' }}
                                -
                                {{ $demande->date_fin_intervention ? $demande->date_fin_intervention->format('d/m/Y H:i') : '?' }}
                            </span>
                        </td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                {{-- Valider --}}
                                <form method="POST" action="{{ route('seg.valider_periode', $demande) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="valider">
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors" title="Valider la période">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Valider
                                    </button>
                                </form>

                                {{-- Rejeter --}}
                                <form method="POST" action="{{ route('seg.rejeter_periode_imputee', $demande) }}" class="inline" 
                                      x-data="{ showMotif: false }" @submit.prevent="if(!showMotif) { showMotif = true; } else { $el.submit(); }">
                                    @csrf
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors" title="Rejeter la période">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Rejeter
                                        </button>
                                        <div x-show="showMotif" x-cloak class="flex items-center gap-1">
                                            <input type="text" name="motif" class="input-senelec text-sm py-1" placeholder="Motif du rejet (min. 10 car.)" required minlength="10">
                                            <button type="submit" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Confirmer</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Aucune période en attente de validation
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
