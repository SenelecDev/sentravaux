@extends('layouts.app')
@section('title', $title ?? 'Demandes UCC')
@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">{{ $title ?? 'Demandes UCC' }}</h1>
    <div class="card-senelec p-4 overflow-x-auto">
        <table class="table-senelec min-w-[900px]">
            <thead><tr><th>N°</th><th>Demandeur</th><th>Nature</th><th>Site</th><th>Statut</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($demandes as $demande)
                @php
                    $statutLower = strtolower((string) ($demande->statut ?? ''));
                    $isClotureStatus = str_starts_with($statutLower, 'clotur');
                @endphp
                <tr>
                    <td>{{ $demande->numero_demande }}</td>
                    <td>{{ $demande->user?->name }}</td>
                    <td>{{ $demande->nature }}</td>
                    <td>{{ $demande->site?->libelle }}</td>
                    <td><x-status-badge :statut="$demande->statut" /></td>
                    <td class="flex items-center gap-2">
                        @if($isClotureStatus)
                            <a href="{{ route('demande.pdf', $demande) }}" target="_blank" class="btn-warning text-xs">Imprimer PDF</a>
                        @else
                            <a href="{{ route('ucc.edit', $demande) }}" class="btn-secondary text-xs">Traiter</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-6 text-gray-500">Aucune demande</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

