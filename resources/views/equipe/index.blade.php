@extends('layouts.app')

@section('title', 'Équipes')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Équipes</h1>
            <p class="mt-1 text-gray-500">{{ count($equipes) }} équipe(s) enregistrée(s)</p>
        </div>
        <a href="{{ route('equipe.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90"
           style="background-color: #2B1444;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter une équipe
        </a>
    </div>

    <div class="card-senelec overflow-hidden">
        <table class="table-senelec">
            <thead>
                <tr>
                    <th>Nom de l'équipe</th>
                    <th>Description</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipes as $equipe)
                <tr>
                    <td class="font-medium text-gray-900">{{ $equipe->nom }}</td>
                    <td class="text-sm text-gray-600">{{ $equipe->description ?? '-' }}</td>
                    <td class="text-right">
                        <a href="{{ route('equipe.edit', $equipe) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-gray-500 py-8">Aucune équipe trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
