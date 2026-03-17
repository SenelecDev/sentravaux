@extends('layouts.app')

@section('title', 'Exécuter la demande - Équipe')

@section('content')
<div class="space-y-6" x-data="{
    teamType: '{{ old('team_type', $demande->team_type ?? 'interne') }}',
    showTerminerModal: false
}">
    {{-- En-tête --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('equipe.demandes.recues') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Exécuter la demande {{ $demande->numero_demande }}</h1>
            <p class="mt-1 text-gray-500">Gestion par le chef d'équipe</p>
        </div>
        <div class="ml-auto">
            <x-status-badge :statut="$demande->statut" />
        </div>
    </div>

    {{-- Informations de la demande --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Informations de la demande</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><p class="text-sm text-gray-500">Objet</p><p class="font-medium text-gray-900">{{ $demande->objet ?? '-' }}</p></div>
                <div><p class="text-sm text-gray-500">Nature</p><p class="font-medium text-gray-900">{{ $demande->nature ?? '-' }}</p></div>
                <div><p class="text-sm text-gray-500">Site</p><p class="font-medium text-gray-900">{{ $demande->site->libelle ?? '-' }}</p></div>
                <div><p class="text-sm text-gray-500">Demandeur</p><p class="font-medium text-gray-900">{{ $demande->user->name ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-500">Service</p><p class="font-medium text-gray-900">{{ $demande->service->libelle ?? '-' }}</p></div>
                <div><p class="text-sm text-gray-500">Observation</p><p class="font-medium text-gray-900">{{ $demande->observation ?? '-' }}</p></div>
                @if($demande->superviseur)
                <div><p class="text-sm text-gray-500">Superviseur</p><p class="font-medium text-gray-900">{{ $demande->superviseur->name }}</p></div>
                @endif
                @if($demande->executant)
                <div><p class="text-sm text-gray-500">Exécutant</p><p class="font-medium text-gray-900">{{ $demande->executant->name }}</p></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Équipes assignées (lecture seule) --}}
    @if($demande->equipes && $demande->equipes->count() > 0)
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Équipes assignées</h2>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @foreach($demande->equipes as $eq)
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-sm">
                        {{ strtoupper(substr($eq->nom, 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $eq->nom }}</p>
                        <p class="text-sm text-gray-500">Durée : {{ $eq->pivot->duree ?? '-' }} jour(s)</p>
                    </div>
                    @if($eq->pivot->executant_id)
                        @php $exec = $users->firstWhere('id', $eq->pivot->executant_id); @endphp
                        @if($exec)
                        <div class="text-sm text-gray-600">Exécutant : {{ $exec->name }}</div>
                        @endif
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(!in_array($demande->statut, ['termine', 'cloture']))
    {{-- Formulaire d'exécution --}}
    <form method="POST" action="{{ route('equipe.update', $demande) }}" id="editForm">
        @csrf
        @method('PUT')

        {{-- Type d'équipe --}}
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Type d'équipe</h2></div>
            <div class="p-6">
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="team_type" value="interne" x-model="teamType" class="text-[#e30613] focus:ring-[#e30613]"><span class="font-medium text-gray-700">Équipe interne</span></label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="team_type" value="externe" x-model="teamType" class="text-[#e30613] focus:ring-[#e30613]"><span class="font-medium text-gray-700">Prestataire externe</span></label>
                </div>
            </div>
        </div>

        {{-- Affectation personnel --}}
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Affectation du personnel</h2></div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chef d'équipe</label>
                        <select name="chef_equipe_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner --</option>
                            @foreach($users as $user)<option value="{{ $user->id }}" {{ old('chef_equipe_id', $demande->chef_equipe_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Superviseur</label>
                        <select name="superviseur_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner --</option>
                            @foreach($users as $user)<option value="{{ $user->id }}" {{ old('superviseur_id', $demande->superviseur_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Exécutant</label>
                        <select name="executant_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner --</option>
                            @foreach($users as $user)<option value="{{ $user->id }}" {{ old('executant_id', $demande->executant_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gestion des équipes --}}
        <div class="card-senelec" x-data="{
            existingEquipes: [@foreach($demande->equipes as $eq){ id: {{ $eq->id }}, nom: '{{ $eq->nom }}', duree: {{ $eq->pivot->duree ?? 1 }}, executant_id: '{{ $eq->pivot->executant_id ?? '' }}' },@endforeach],
            newEquipes: [],
            addNewEquipe() { this.newEquipes.push({ nom: '', duree: 1, executant_id: '' }); },
            removeNewEquipe(index) { this.newEquipes.splice(index, 1); }
        }">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Équipes affectées</h2>
                    <button type="button" @click="addNewEquipe()" class="btn-senelec-outline text-sm py-1.5 px-3">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Ajouter
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <template x-for="(eq, index) in existingEquipes" :key="'existing-' + index">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Équipe</label><input type="hidden" :name="'equipes[' + index + ']'" :value="eq.id"><p class="font-medium text-gray-900" x-text="eq.nom"></p></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Durée (jours)</label><input type="number" :name="'duree[' + index + ']'" x-model="eq.duree" min="0.5" step="0.5" class="input-senelec w-full"></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Exécutant</label><select :name="'executant_equipe[' + index + ']'" x-model="eq.executant_id" class="select-senelec w-full"><option value="">-- Aucun --</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                        <div class="flex items-end"><span class="badge-info text-xs">Existante</span></div>
                    </div>
                </template>
                <template x-for="(eq, index) in newEquipes" :key="'new-' + index">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Nom</label><select :name="'equipes_noms_new[' + index + ']'" x-model="eq.nom" class="select-senelec w-full"><option value="">-- Choisir --</option>@foreach($equipes as $equipe)<option value="{{ $equipe->nom }}">{{ $equipe->nom }}</option>@endforeach</select></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Durée</label><input type="number" :name="'duree_new[' + index + ']'" x-model="eq.duree" min="0.5" step="0.5" class="input-senelec w-full"></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Exécutant</label><select :name="'executant_equipe_new[' + index + ']'" x-model="eq.executant_id" class="select-senelec w-full"><option value="">-- Aucun --</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                        <div class="flex items-end"><button type="button" @click="removeNewEquipe(index)" class="btn-danger text-sm py-1.5 px-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
                    </div>
                </template>
                <template x-if="existingEquipes.length === 0 && newEquipes.length === 0">
                    <p class="text-gray-500 text-center py-4">Aucune équipe affectée.</p>
                </template>
            </div>
        </div>

        {{-- Prestataire --}}
        <div class="card-senelec" x-show="teamType === 'externe'" x-transition>
            <div class="p-6 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Prestataire externe</h2></div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Nom du prestataire</label><input type="text" name="prestataire_nom" value="{{ old('prestataire_nom', $demande->prestataire_nom) }}" class="input-senelec w-full"></div>
                </div>
            </div>
        </div>

        {{-- Commentaire --}}
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Commentaire</h2></div>
            <div class="p-6">
                <textarea name="commentaire_equipe" rows="3" class="input-senelec w-full" placeholder="Ajouter un commentaire...">{{ old('commentaire_equipe', $demande->commentaire_equipe) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card-senelec">
            <div class="p-6">
                <div class="flex flex-wrap gap-3 justify-end">
                    <button type="submit" name="action" value="dispatcher" class="btn-senelec"
                        onclick="return confirm('Exécuter cette demande ?')">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Exécuter
                    </button>
                    <button type="button" @click="showTerminerModal = true" class="btn-success">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Terminer
                    </button>
                </div>
            </div>
        </div>
    </form>
    @else
    {{-- Demande terminée/clôturée --}}
    <div class="card-senelec">
        <div class="p-6 text-center">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-lg font-semibold text-gray-900">Cette demande est {{ $demande->statut === 'cloture' ? 'clôturée' : 'terminée' }}</p>
            <p class="text-gray-500 mt-1">Aucune modification n'est possible.</p>
        </div>
    </div>
    @endif
</div>

{{-- Modal Terminer --}}
<template x-teleport="body">
    <div x-show="showTerminerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="showTerminerModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Terminer la demande</h3>
            <p class="text-gray-600 mb-4">Confirmez-vous que les travaux sont terminés ?</p>
            <form method="POST" action="{{ route('equipe.update', $demande) }}">
                @csrf @method('PUT')
                <input type="hidden" name="team_type" value="{{ $demande->team_type ?? 'interne' }}">
                <input type="hidden" name="action" value="terminer">
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showTerminerModal = false" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-success">Terminer</button>
                </div>
            </form>
        </div>
    </div>
</template>
@endsection
