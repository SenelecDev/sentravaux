@extends('layouts.app')

@section('title', 'Gérer la demande - UMR')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 6px 12px;
        background-color: #fff;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
        right: 8px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal;
        color: #374151;
        padding-left: 0;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        display: none;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2B1444;
        box-shadow: 0 0 0 2px rgba(43, 20, 68, 0.15);
    }
    .select2-dropdown {
        border-radius: 0.5rem;
        border-color: #d1d5db;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 0.375rem;
        border-color: #d1d5db;
        padding: 8px 12px;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-8" x-data="{
    teamType: '{{ old('team_type', $demande->team_type ?? 'interne') }}',
    showTerminerModal: {{ old('action') === 'terminer' ? 'true' : 'false' }},
    showRetourModal: false,
    showCloturerModal: false
}">
    {{-- En-tête --}}
    <div class="flex items-center gap-4 bg-white rounded-xl shadow-sm px-6 py-4 border border-gray-100">
        <a href="{{ route('umr.demandes.recues') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">Gérer la demande {{ $demande->numero_demande }}</h1>
            <p class="mt-1 text-gray-500">Unité Matériel Roulant (UMR) &middot; {{ $demande->objet ?? '-' }}</p>
            <p class="mt-0.5 text-xs text-gray-400">
                Créée le {{ $demande->created_at?->format('d/m/Y H:i') }}
                @if($demande->date_debut_intervention)
                    &middot; Début travaux {{ $demande->date_debut_intervention?->format('d/m/Y H:i') }}
                @endif
                @if($demande->date_fin_intervention)
                    &middot; Fin travaux {{ $demande->date_fin_intervention?->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <div class="ml-auto">
            <x-status-badge :statut="$demande->statut" />
        </div>
    </div>

    @php
        $isTermine = strtolower($demande->statut ?? '') === 'termine';
    @endphp

    {{-- Informations de la demande --}}
    <div class="card-senelec">
        <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Informations de la demande</h2>
                <p class="mt-1 text-xs text-gray-500">Détails fonctionnels pour l'exécution des travaux.</p>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <p class="text-sm text-gray-500">Objet</p>
                    <p class="font-medium text-gray-900">{{ $demande->objet ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nature</p>
                    <p class="font-medium text-gray-900">{{ $demande->nature ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Site</p>
                    <p class="font-medium text-gray-900">{{ $demande->site->libelle ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Demandeur</p>
                    <p class="font-medium text-gray-900">{{ $demande->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Service</p>
                    <p class="font-medium text-gray-900">
                        @if($demande->service)
                            {{ $demande->service->libelle }}
                        @elseif($demande->departement)
                            {{ $demande->departement->libelle }}
                        @elseif($demande->direction)
                            {{ $demande->direction->libelle }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <p class="text-sm text-gray-500">Observation</p>
                    <p class="mt-1 text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2 min-h-[42px]">
                        {{ $demande->observation ?? '-' }}
                    </p>
                </div>
                @if($demande->date_intervention)
                <div>
                    <p class="text-sm text-gray-500">Date d'intervention planifiée</p>
                    <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($demande->date_intervention)->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($demande->terminatedBy)
                <div>
                    <p class="text-sm text-gray-500">Terminée par</p>
                    <p class="font-medium text-gray-900">{{ $demande->terminatedBy->name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    {{-- Période d'intervention validée (UMR) --}}
    @php
        $periodeUmrValidee = $demande->periode_validee_seg ?? false;
    @endphp
    @if($periodeUmrValidee && $demande->date_debut_intervention && $demande->date_fin_intervention)
        <div class="card-senelec border border-teal-500 shadow-sm">
            <div class="p-4 rounded-t-xl flex items-center justify-between"
                style="background: linear-gradient(90deg,#0f766e,#14b8a6); color: #ffffff;">
                <div>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">Période d'intervention (UMR)</h2>
                    <p class="mt-0.5 text-xs text-teal-100">Période validée par le chef SEG</p>
                </div>
            </div>
            <div class="p-4 space-y-3" style="background-color:#ecfdf5;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Date de début</label>
                        <input type="text" class="input-senelec bg-gray-100 text-sm" disabled
                            value="{{ \Carbon\Carbon::parse($demande->date_debut_intervention)->format('d/m/Y') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Date de fin</label>
                        <input type="text" class="input-senelec bg-gray-100 text-sm" disabled
                            value="{{ \Carbon\Carbon::parse($demande->date_fin_intervention)->format('d/m/Y') }}">
                    </div>
                </div>
                <p class="text-xs text-gray-600">
                    Période : du
                    <span class="font-semibold">
                        {{ \Carbon\Carbon::parse($demande->date_debut_intervention)->format('d/m/Y') }}
                    </span>
                    au
                    <span class="font-semibold">
                        {{ \Carbon\Carbon::parse($demande->date_fin_intervention)->format('d/m/Y') }}
                    </span>
                </p>
            </div>
        </div>
    @endif

    @if($demande->statut !== 'cloture')
    {{-- Formulaire de gestion --}}
    <form method="POST" action="{{ route('umr.update', $demande) }}" id="editForm" class="space-y-6">
        @csrf
        @method('PUT')

        <fieldset @if($isTermine) disabled @endif class="space-y-6">

            {{-- Type d'équipe --}}
            @if(!in_array(strtolower($demande->statut), ['termine', 'en_cours']))
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Type d'équipe</h2>
                </div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="team_type" value="interne" x-model="teamType" class="text-[#e30613] focus:ring-[#e30613]">
                            <span class="font-medium text-gray-700">Équipe interne</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="team_type" value="externe" x-model="teamType" class="text-[#e30613] focus:ring-[#e30613]">
                            <span class="font-medium text-gray-700">Prestataire externe</span>
                        </label>
                    </div>
                    @error('team_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            @endif

            {{-- Affectation personnel --}}
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Affectation du personnel</h2>
                </div>
                <div class="p-6">
                    <div class="grid gap-6"
                         :class="teamType === 'externe' ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-3'">
                        {{-- Chef d'équipe (interne uniquement) --}}
                        <div x-show="teamType === 'interne'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chef d'équipe</label>
                            <select name="chef_equipe_id" class="select2-users" @if($isTermine) disabled @endif>
                                <option value="">-- Sélectionner --</option>
                                @foreach(($chefEquipeUsers ?? $users) as $user)
                                    <option value="{{ $user->id }}" {{ old('chef_equipe_id', $demande->chef_equipe_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} @if($user->matricule) - {{ $user->matricule }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('chef_equipe_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Superviseur (interne) --}}
                        <div x-show="teamType === 'interne'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Superviseur</label>
                            <select name="superviseur_id" class="select2-users" @if($isTermine) disabled @endif>
                                <option value="">-- Sélectionner --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('superviseur_id', $demande->superviseur_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} @if($user->matricule) - {{ $user->matricule }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('superviseur_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Superviseur externe + Prestataire (mode externe) --}}
                        <div x-show="teamType === 'externe'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Superviseur</label>
                            <select name="superviseur_externe_id" class="select2-users" @if($isTermine) disabled @endif>
                                <option value="">-- Sélectionner --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('superviseur_externe_id', $demande->superviseur_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} @if($user->matricule) - {{ $user->matricule }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="teamType === 'externe'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prestataire</label>
                            <input type="text" name="prestataire_nom" value="{{ old('prestataire_nom', $demande->prestataire_nom) }}" class="input-senelec w-full" placeholder="Nom du prestataire" @if($isTermine) disabled @endif>
                        </div>

                        {{-- Exécutant (interne uniquement) --}}
                        <div x-show="teamType === 'interne'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Exécutant</label>
                            <select name="executant_id" class="select2-users" @if($isTermine) disabled @endif>
                                <option value="">-- Sélectionner --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('executant_id', $demande->executant_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} @if($user->matricule) - {{ $user->matricule }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('executant_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

        

        {{-- Gestion des équipes --}}
        @php
            $existingEquipes = $demande->equipes->unique('nom');
        @endphp
        <div class="card-senelec"
             x-data="{
                newEquipes: [],
                addNewEquipe() {
                    this.newEquipes.push({ nom: '', duree: 1, executant_id: '' });
                },
                removeNewEquipe(index) {
                    this.newEquipes.splice(index, 1);
                }
             }">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Équipes affectées</h2>
                    @if(!$isTermine)
                    <button type="button" @click="addNewEquipe()" class="btn-senelec-outline text-sm py-1.5 px-3">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Ajouter
                    </button>
                    @endif
                </div>
            </div>
            <div class="p-6 space-y-4">
                {{-- Équipes existantes (rendu Blade pour éviter toute duplication visuelle) --}}
                @foreach($existingEquipes as $index => $eq)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Équipe</label>
                            <input type="hidden" name="equipes[{{ $index }}]" value="{{ $eq->id }}">
                            <p class="font-medium text-gray-900">{{ $eq->nom }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Durée (jours)</label>
                            <input type="number"
                                   name="duree[{{ $index }}]"
                                   value="{{ old('duree.' . $index, $eq->pivot->duree ?? 1) }}"
                                   min="0.5"
                                   step="0.5"
                                   class="input-senelec w-full"
                                   @if($isTermine) disabled @endif>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Exécutant</label>
                            <select name="executant_equipe[{{ $index }}]" class="select2-users" @if($isTermine) disabled @endif>
                                <option value="">-- Aucun --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        @if(old('executant_equipe.' . $index, $eq->pivot->executant_id ?? null) == $user->id) selected @endif>
                                        {{ $user->name }} @if($user->matricule) - {{ $user->matricule }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-between md:justify-end gap-2">
                            <span class="badge-info text-xs">Existante</span>
                            @if($demande->statut !== 'termine')
                                <button type="button"
                                        onclick="this.closest('.grid').remove();"
                                        class="btn-danger text-xs py-1.5 px-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Nouvelles équipes --}}
                @if(!$isTermine)
                <template x-for="(eq, index) in newEquipes" :key="'new-' + index">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nom de l'équipe</label>
                            <select :name="'equipes_noms_new[' + index + ']'" x-model="eq.nom" class="select-senelec w-full">
                                <option value="">-- Choisir --</option>
                                @foreach($equipes as $equipe)
                                    <option value="{{ $equipe->nom }}">{{ $equipe->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Durée (jours)</label>
                            <input type="number" :name="'duree_new[' + index + ']'" x-model="eq.duree" min="0.5" step="0.5" class="input-senelec w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Exécutant</label>
                            <select :name="'executant_equipe_new[' + index + ']'" x-model="eq.executant_id" class="select2-users">
                                <option value="">-- Aucun --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} @if($user->matricule) - {{ $user->matricule }} @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-end">
                            <button type="button" @click="removeNewEquipe(index)" class="btn-danger text-sm py-1.5 px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
                @endif

                <template x-if="{{ $existingEquipes->count() }} === 0 && newEquipes.length === 0">
                    <p class="text-gray-500 text-center py-4">Aucune équipe affectée. Cliquez sur "Ajouter" pour en affecter.</p>
                </template>
            </div>
        </div>

        {{-- Prestataire (infos complémentaires en mode externe) --}}
        <div class="card-senelec" x-show="teamType === 'externe'" x-transition>
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Prestataire externe</h2>
                <p class="mt-1 text-xs text-gray-500">Complétez les informations complémentaires liées au prestataire choisi.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">N° de commande</label>
                        <input type="text" name="numero_commande" value="{{ old('numero_commande', $demande->numero_commande) }}" class="input-senelec w-full" placeholder="Numéro de commande" @if($isTermine) disabled @endif>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire prestataire</label>
                        <input type="text" name="commentaire_prestataire" value="{{ old('commentaire_prestataire', $demande->commentaire_prestataire) }}" class="input-senelec w-full" placeholder="Commentaire" @if($isTermine) disabled @endif>
                    </div>
                </div>
            </div>
        </div>

        {{-- Commentaire --}}
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Commentaire</h2>
            </div>
            <div class="p-6">
                <textarea name="commentaire" rows="3" class="input-senelec w-full" placeholder="Ajouter un commentaire..." @if($isTermine) disabled @endif>{{ old('commentaire', $demande->comment_umr) }}</textarea>

                @if($demande->commentaire_equipe)
                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm font-medium text-blue-800 mb-1">Commentaire du chef d'équipe :</p>
                    <p class="text-sm text-blue-700 whitespace-pre-line">{{ $demande->commentaire_equipe }}</p>
                </div>
                @endif
            </div>
        </div>

        </fieldset>

        {{-- Actions --}}
        <div class="mt-6">
            <div class="flex flex-col md:flex-row gap-3">
                @if($demande->statut !== 'termine')
                    {{-- Sauvegarder toujours visible tant que la demande n'est pas terminée --}}
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-200 shadow-sm hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Sauvegarder
                    </button>

                    {{-- Valider & dispatcher (UMR) --}}
                    @if(in_array($demande->statut, ['impute', 'accepte']) && auth()->user()->hasRole('umr'))
                        <button type="submit" name="action" value="dispatcher"
                                class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-lg text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
                                style="background-color: #0D1CB0;"
                                onclick="return confirm('Valider et dispatcher cette demande au chef d\'équipe ?')">
                            Valider & dispatcher
                        </button>
                    @endif

                    {{-- Bloc travaux (valide / en_cours) --}}
                    @if(strtolower($demande->statut) === 'valide')
                        <div class="flex-1 flex flex-col md:flex-row gap-3">
                            @if(auth()->user()->hasRole('umr'))
                                <button type="button"
                                        @click="showRetourModal = true"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
                                        style="background-color: #f97316;">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    Retour
                                </button>
                            @endif
                            <button type="submit" name="action" value="debut_travaux"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-white bg-emerald-600 shadow-sm hover:bg-emerald-700 transition"
                                    onclick="return confirm('Confirmer le début des travaux ?')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Débuter les travaux
                            </button>
                        </div>
                    @elseif($demande->statut === 'en_cours')
                        <button type="button"
                                @click="showTerminerModal = true"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-white bg-emerald-600 shadow-sm hover:bg-emerald-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Terminer les travaux
                        </button>
                    @endif
                @endif

                {{-- Clôture --}}
                @if($demande->statut === 'termine' && auth()->user()->hasRole('umr'))
                    <button type="button"
                            @click="showCloturerModal = true"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-white bg-red-600 shadow-sm hover:bg-red-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Clôturer la demande
                    </button>
                {{-- @elseif(!in_array($demande->statut, ['termine','cloture']))
                    <div class="flex-1 hidden md:flex items-center justify-center text-xs text-red-600">
                        Clôture possible uniquement après la fin des travaux.
                    </div> --}}
                @endif
            </div>
        </div>
    </form>
    @else
    {{-- Demande clôturée --}}
    <div class="card-senelec">
        <div class="p-6 text-center">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-lg font-semibold text-gray-900">Cette demande est clôturée</p>
            <p class="text-gray-500 mt-1">Aucune modification n'est possible.</p>
        </div>
    </div>
    @endif

    {{-- Modal Terminer --}}
    <template x-teleport="body">
    <div x-show="showTerminerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="showTerminerModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Terminer la demande</h3>
            <form method="POST" action="{{ route('umr.update', $demande) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="terminer">
                <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date et heure d'intervention *</label>
                            <input type="datetime-local"
                                   name="date_intervention"
                                   value="{{ old('date_intervention', now()->format('Y-m-d\TH:i')) }}"
                                   class="input-senelec w-full @error('date_intervention') border-red-500 @enderror"
                                   required>
                            @error('date_intervention')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Ces champs ne concernent que les prestations externes --}}
                        <div x-show="teamType === 'externe'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prestataire (optionnel)</label>
                            <input type="text" name="prestataire_nom" value="{{ old('prestataire_nom', $demande->prestataire_nom) }}" class="input-senelec w-full" placeholder="Nom du prestataire">
                        </div>
                        <div x-show="teamType === 'externe'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° commande</label>
                            <input type="text"
                                   name="numero_commande"
                                   value="{{ old('numero_commande', $demande->numero_commande) }}"
                                   class="input-senelec w-full @error('numero_commande') border-red-500 @enderror"
                                   placeholder="Numéro de commande">
                            @error('numero_commande')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showTerminerModal = false" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-success">Terminer</button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Modal Retour --}}
    <template x-teleport="body">
    <div x-show="showRetourModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="showRetourModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Retour pour correction</h3>
            <form method="POST" action="{{ route('umr.update', $demande) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="retour">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif du retour</label>
                    <textarea name="commentaire_retour" rows="3" class="input-senelec w-full" placeholder="Expliquer le motif du retour..."></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showRetourModal = false" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-warning">Renvoyer</button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Modal Clôturer --}}
    <template x-teleport="body">
    <div x-show="showCloturerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="showCloturerModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Clôturer la demande</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir clôturer cette demande ? Un PDF sera généré et envoyé au demandeur.</p>
            <form method="POST" action="{{ route('umr.update', $demande) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="cloturer">
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showCloturerModal = false" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-danger">Clôturer</button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>
<script>
    $(function () {
        function initSelect2(scope) {
            var $selects = $(scope).find('.select2-users').not('.select2-hidden-accessible');
            $selects.select2({
                language: 'fr',
                placeholder: '-- Sélectionner --',
                allowClear: true,
                width: '100%'
            });
            $selects.on('select2:select select2:clear', function () {
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        initSelect2(document);

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        initSelect2(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
</script>
@endpush
