@extends('layouts.app')

@section('title', 'Gestion des services')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des services</h1>
            <p class="mt-1 text-gray-500">{{ $services->total() }} service(s) trouvé(s)</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card-senelec p-4">
        <form action="{{ route('admin.services.index') }}" method="GET" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher un service..." class="input-senelec">
            </div>
            <button type="submit" class="btn-senelec">Filtrer</button>
            <a href="{{ route('admin.services.index') }}" class="btn-secondary px-4">
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
                        <th>Service</th>
                        <th>Nombre d'utilisateurs</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $svc)
                        <tr>
                            <td class="font-medium text-gray-900">
                                {{ $svc->libelle }}
                                @if($svc->code && $svc->code !== $svc->libelle)
                                    <span class="text-xs text-gray-500 block">{{ $svc->code }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                                    {{ $svc->users_count }} utilisateur(s)
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.services.show', $svc->libelle) }}" 
                                   class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors inline-block" title="Voir les utilisateurs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">
                                Aucun service trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($services->hasPages())
    <div>
        {{ $services->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
