@extends('layouts.app')

@section('title', 'Demandes SGB')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">Toutes les demandes SGB</h1>
    <div class="card-senelec p-4 overflow-x-auto">
        <table class="table-senelec min-w-[900px]">
            <thead><tr><th>N°</th><th>Demandeur</th><th>Nature</th><th>Service</th><th>Site</th><th>Statut</th></tr></thead>
            <tbody>
            @forelse($demandes as $demande)
                <tr class="cursor-pointer hover:bg-gray-50" onclick="window.location='{{ route('demande.show', $demande) }}'">
                    <td>{{ $demande->numero_demande }}</td>
                    <td>{{ $demande->user?->name }}</td>
                    <td>{{ $demande->nature }}</td>
                    <td>{{ $demande->service?->libelle }}</td>
                    <td>{{ $demande->site?->libelle }}</td>
                    <td><x-status-badge :statut="$demande->statut" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-6 text-gray-500">Aucune demande</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

