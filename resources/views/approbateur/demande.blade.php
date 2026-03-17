@extends('layouts.app')

@section('title', 'Demandes à approuver')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes à approuver</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) en attente</p>
    </div>

    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[1000px]">
                <thead>
                    <tr>
                        <th>N°Demande</th>
                        <th>Demandeur</th>
                        <th>Objet</th>
                        <th>Nature</th>
                        <th>Site</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr class="cursor-pointer hover:bg-gray-50 transition-colors" onclick="window.location='{{ route('demande.show', $demande) }}'">
                        <td><span class="font-mono text-sm font-semibold text-senelec-purple">{{ $demande->numero_demande }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                @if(!empty($demande->user->photo))
                                    <img src="{{ asset('storage/' . $demande->user->photo) }}" alt="Photo" class="w-8 h-8 rounded-full object-cover border border-gray-300" />
                                @else
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($demande->user->name ?? '', 0, 2)) }}
                                    </div>
                                @endif
                                <div><p class="text-sm font-medium">{{ $demande->user->name ?? 'N/A' }}</p></div>
                            </div>
                        </td>
                        <td class="text-sm">{{ Str::limit($demande->objet, 40) }}</td>
                        <td class="text-sm">{{ $demande->nature ?? '-' }}</td>
                        <td class="text-sm">{{ $demande->site->libelle ?? '-' }}</td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                        <td class="text-sm text-gray-500">{{ $demande->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('demande.show', $demande) }}" class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aucune demande en attente d'approbation
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
