@extends('layouts.app')

@section('title', 'Gestion des sites')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des sites</h1>
            <p class="mt-1 text-gray-500">{{ $sites->total() }} site(s) trouvé(s)</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card-senelec p-4">
        <form action="{{ route('admin.sites.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher un site (libellé, code, ville, région)..." class="input-senelec">
            </div>
            <div class="w-48">
                <select name="status" class="input-senelec">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactifs</option>
                </select>
            </div>
            <button type="submit" class="btn-senelec">Filtrer</button>
            <a href="{{ route('admin.sites.index') }}" class="btn-secondary px-4 flex items-center">
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
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Ville</th>
                        <th>Région</th>
                        <th>Demandes</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        <tr>
                            <td class="font-mono text-sm text-gray-600">{{ $site->code ?? '—' }}</td>
                            <td class="font-medium text-gray-900">{{ $site->libelle }}</td>
                            <td>{{ $site->ville ?? '—' }}</td>
                            <td>{{ $site->region ?? '—' }}</td>
                            <td>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                                    {{ $site->demandes_count }} demande(s)
                                </span>
                            </td>
                            <td>
                                @if($site->is_active)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Actif</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Inactif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.sites.show', $site) }}" 
                                   class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors inline-block" title="Voir le site">
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
                                Aucun site trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($sites->hasPages())
    <div>
        {{ $sites->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
