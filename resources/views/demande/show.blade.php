@extends('layouts.app')

@section('title', 'Demande ' . $demande->numero_demande)

@section('content')
    <div class="space-y-8">
    {{-- Header --}}
    @php
        $user = auth()->user();
        $backUrl = url()->previous();
        $currentUrl = url()->current();

        // Si le précédent est la même page (ou vide), on choisit en fonction du rôle
        if (!$backUrl || $backUrl === $currentUrl) {
            if ($user->hasRole('approbateur')) {
                $backUrl = route('demandes.aapprouver');
            } elseif ($user->hasRole('sad')) {
                $backUrl = route('sad.demandes');
            } elseif ($user->hasRole('seg')) {
                $backUrl = route('seg.demandes');
            } elseif ($user->hasRole('demandeur')) {
                $backUrl = route('demande.index');
            } elseif ($user->hasRole('umt')) {
                $backUrl = route('umt.demandes.recues');
            } elseif ($user->hasRole('ubt')) {
                $backUrl = route('ubt.demandes.recues');
            } elseif ($user->hasRole('unsp')) {
                $backUrl = route('unsp.demandes.recues');
            } elseif ($user->hasRole('umr')) {
                $backUrl = route('umr.demandes.recues');
            } elseif ($user->hasRole('utgc')) {
                $backUrl = route('utgc.demandes.recues');
            } elseif ($user->hasRole('equipe')) {
                $backUrl = route('equipe.demandes.recues');
            } else {
                $backUrl = route('dashboard');
            }
        }
    @endphp

    <div class="flex items-center justify-between bg-white rounded-xl shadow-sm px-6 py-4 border border-gray-100">
        <a href="{{ $backUrl }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">Demande {{ $demande->numero_demande }}</h1>
            <p class="mt-1 text-sm text-gray-500">Créée le {{ $demande->created_at?->format('d/m/Y \u00e0 H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->id() === $demande->user_id && in_array(strtolower($demande->statut), ['brouillon']))
                <a href="{{ route('demande.edit', $demande) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90" style="background-color: #3498db;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier
                </a>
                <form action="{{ route('demande.submit', $demande) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90" style="background-color: #e67e22;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Soumettre
                    </button>
                </form>
                <form action="{{ route('demande.destroy', $demande) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90" style="background-color: #e74c3c;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Supprimer
                    </button>
                </form>
            @endif
            @if(auth()->id() === $demande->user_id && in_array(strtolower($demande->statut), ['rejete', 'rejete']))
                <a href="{{ route('demande.edit', $demande) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90" style="background-color: #3498db;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier
                </a>
            @endif
            
            {{-- Statut + action de traitement --}}
            <div class="flex items-center gap-3">
                <x-status-badge :statut="$demande->statut" />

                @php
                    $unitRoleToRouteHeader = [
                        'umt' => 'umt.edit',
                        'ubt' => 'ubt.edit',
                        'unsp' => 'unsp.edit',
                        'umr' => 'umr.edit',
                        'utgc' => 'utgc.edit',
                        'equipe' => 'equipe.edit',
                    ];
                    $currentUserHeader = auth()->user();
                    $unitEditRouteHeader = null;

                    if ($currentUserHeader) {
                        foreach ($unitRoleToRouteHeader as $role => $route) {
                            if ($role === 'equipe' && $currentUserHeader->hasRole('equipe') && $demande->chef_equipe_id === $currentUserHeader->id) {
                                $unitEditRouteHeader = $route;
                                break;
                            }
                            if ($role !== 'equipe' && $currentUserHeader->hasRole($role) && $demande->$role) {
                                $unitEditRouteHeader = $route;
                                break;
                            }
                        }
                    }
                @endphp

                @php
                    $statutLower = strtolower($demande->statut ?? '');
                    $isEquipeUser = auth()->user() && auth()->user()->hasRole('equipe');
                @endphp

                {{-- Bouton traiter la demande (sauf pour équipe sur \"terminée\" et demandes clôturées) --}}
                @if($unitEditRouteHeader && $statutLower !== 'cloture' && !($isEquipeUser && $statutLower === 'termine'))
                    <a href="{{ route($unitEditRouteHeader, $demande) }}"
                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-senelec-purple text-white text-xs font-semibold hover:bg-senelec-purple/90 transition-colors"
                       title="Traiter la demande">
                        Traiter la demande
                    </a>
                @endif

                {{-- Bouton Imprimer PDF pour les demandes clôturées --}}
                @if($statutLower === 'cloture' && Route::has('demande.pdf'))
                    <a href="{{ route('demande.pdf', $demande) }}"
                       target="_blank"
                       class="btn-warning text-xs"
                       title="Ouvrir le PDF">
                        Imprimer le PDF
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Informations principales --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Informations de la demande</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Objet</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $demande->objet ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nature</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $demande->nature ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Site</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $demande->site->libelle ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Unité</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $demande->unite_label ?? '-' }}</dd>
                        </div>
                        @if($demande->date_debut_intervention)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Début intervention</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $demande->date_debut_intervention?->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                        @if($demande->date_fin_intervention)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fin intervention</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $demande->date_fin_intervention?->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                        @if($demande->type_prestation)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type prestation</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($demande->type_prestation) }}</dd>
                        </div>
                        @endif
                        @if($demande->team_type)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type d'équipe</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($demande->team_type) }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($demande->observation)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Observation</dt>
                        <dd class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 p-4 rounded-lg">{{ $demande->observation }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Historique workflow --}}
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Suivi du workflow</h2>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Demande créée</p>
                                        <p class="text-xs text-gray-500">Par {{ $demande->user->name ?? 'N/A' }} le {{ $demande->created_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </li>
                            @if($demande->approvedBy)
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Approuvée</p>
                                        <p class="text-xs text-gray-500">Par {{ $demande->approvedBy->name }}</p>
                                        @if($demande->commentaire_approbation)
                                            <p class="text-xs text-gray-600 mt-1 italic">{{ $demande->commentaire_approbation }}</p>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            @endif

                            @php
                                $serviceDisplay = [
                                    'sad' => 'Service Administratif (SA)',
                                    'seg' => 'Service Entretien Général (SEG)',
                                ];
                            @endphp

                            @if($demande->sad || $demande->seg)
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Affectée au service</p>
                                        <p class="text-xs text-gray-500">
                                            @if($demande->sad)
                                                {{ $serviceDisplay['sad'] }} - {{ $demande->sad->name }}
                                            @endif
                                            @if($demande->sad && $demande->seg)
                                                &middot;
                                            @endif
                                            @if($demande->seg)
                                                {{ $serviceDisplay['seg'] }} - {{ $demande->seg->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </li>
                            @endif

                            @php
                                $unitLabels = [
                                    // SA units (rôles umt/ubt/unsp)
                                    'umt' => 'Unité Affaire Générale (UAG)',
                                    'ubt' => 'Unité Gestion Baux & Taxes (UGBT)',
                                    'unsp' => 'Unité Pool Néttoiement Sécurité (UPNS)',
                                    // SEG units
                                    'utgc' => 'Unité Travaux Génie Civil (UTGC)',
                                    'umr' => 'Unité Matériel Roulant (UMR)',
                                ];
                                $imputedUnits = [];
                                foreach ($unitLabels as $key => $label) {
                                    if ($demande->$key) {
                                        $chiefName = $demande->$key->name ?? null;
                                        $imputedUnits[] = $chiefName
                                            ? $label . ' - ' . $chiefName
                                            : $label;
                                    }
                                }
                            @endphp

                            @if(count($imputedUnits) > 0)
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-senelec-magenta flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Imputée vers l'unité</p>
                                        <p class="text-xs text-gray-500">
                                            {{ implode(' · ', $imputedUnits) }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($demande->rejectedBy)
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Rejetée</p>
                                        <p class="text-xs text-gray-500">Par {{ $demande->rejectedBy->name }}</p>
                                        @if($demande->motif)
                                            <p class="text-xs text-red-600 mt-1">Motif : {{ $demande->motif }}</p>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($demande->validatedBy)
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Validée</p>
                                        <p class="text-xs text-gray-500">Par {{ $demande->validatedBy->name }}</p>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($demande->terminatedBy)
                            <li class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Terminée</p>
                                        <p class="text-xs text-gray-500">Par {{ $demande->terminatedBy->name }}</p>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($demande->cloturedBy)
                            <li class="relative">
                                <div class="relative flex space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 font-medium">Clôturée</p>
                                        <p class="text-xs text-gray-500">Par {{ $demande->cloturedBy->name }}</p>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Images --}}
            @if($demande->images && $demande->images->count() > 0)
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Photos</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($demande->images as $image)
                        <a href="{{ asset('storage/' . $image->path) }}" target="_blank" class="block group">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->original_name }}" 
                                 class="w-full h-40 object-cover rounded-lg border border-gray-200 group-hover:border-senelec-purple/50 transition-colors">
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ $image->original_name }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

            {{-- Panneau latéral --}}
        <div class="space-y-6">
            {{-- Demandeur --}}
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Demandeur</h3>
                </div>
                <div class="p-6 flex items-center gap-3">
                    @if($demande->user && $demande->user->photo_url)
                        <img src="{{ $demande->user->photo_url }}" alt="{{ $demande->user->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-full bg-senelec-purple flex items-center justify-center text-white font-semibold shrink-0">
                            {{ strtoupper(substr($demande->user->name ?? '', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-medium text-gray-900">{{ $demande->user->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $demande->user->service ?? '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Affectations --}}
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Affectations</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if($demande->approbateurN1)
                    <div><span class="text-xs text-gray-500 block">Approbateur</span><span class="text-sm font-medium">{{ $demande->approbateurN1->name }}</span></div>
                    @endif
                    @if($demande->sad)
                    <div>
                        <span class="text-xs text-gray-500 block">Service</span>
                        <span class="text-sm font-medium">{{ $serviceDisplay['sad'] }} - {{ $demande->sad->name }}</span>
                    </div>
                    @endif
                    @if($demande->seg)
                    <div>
                        <span class="text-xs text-gray-500 block">Service</span>
                        <span class="text-sm font-medium">{{ $serviceDisplay['seg'] }} - {{ $demande->seg->name }}</span>
                    </div>
                    @endif
                    @php
                        $unitDisplayNames = [
                            'umt' => 'Unité Affaire Générale (UAG)',
                            'ubt' => 'Unité Gestion Baux & Taxes (UGBT)',
                            'unsp' => 'Unité Pool Néttoiement Sécurité (UPNS)',
                            'umr' => 'Unité Matériel Roulant (UMR)',
                            'utgc' => 'Unité Travaux Génie Civil (UTGC)',
                        ];
                    @endphp
                    @foreach(['umt', 'ubt', 'unsp', 'umr', 'utgc'] as $unit)
                        @if($demande->$unit)
                            <div>
                                <span class="text-xs text-gray-500 block">Unité</span>
                                <span class="text-sm font-medium">
                                    {{ $unitDisplayNames[$unit] ?? strtoupper($unit) }}
                                </span>
                            </div>
                            @if($demande->$unit->name)
                                <div>
                                    <span class="text-xs text-gray-500 block">Chef d'unité</span>
                                    <span class="text-sm font-medium">
                                        {{ $demande->$unit->name }}
                                    </span>
                                </div>
                            @endif
                        @endif
                    @endforeach
                    @if($demande->chefequipe)
                    <div><span class="text-xs text-gray-500 block">Chef d'équipe</span><span class="text-sm font-medium">{{ $demande->chefequipe->name }}</span></div>
                    @endif
                    @if($demande->superviseur)
                    <div><span class="text-xs text-gray-500 block">Superviseur</span><span class="text-sm font-medium">{{ $demande->superviseur->name }}</span></div>
                    @endif
                    @if($demande->executant)
                    <div><span class="text-xs text-gray-500 block">Exécutant</span><span class="text-sm font-medium">{{ $demande->executant->name }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Équipes --}}
            @if($demande->equipes && $demande->equipes->count() > 0)
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Équipes</h3>
                </div>
                <div class="p-6 space-y-2">
                    @foreach($demande->equipes as $equipe)
                        @php
                            $executantEquipe = $equipe->pivot->executant_id
                                ? \App\Models\User::find($equipe->pivot->executant_id)
                                : null;
                        @endphp
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">{{ $equipe->nom }}</span>
                                @if($executantEquipe)
                                    <span class="text-xs text-gray-500">Exécutant : {{ $executantEquipe->name }}</span>
                                @endif
                            </div>
                            @if($equipe->pivot->duree)
                                <span class="text-xs text-gray-500">{{ $equipe->pivot->duree }}h</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Prestataire --}}
            @if($demande->prestataire_nom)
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Prestataire</h3>
                </div>
                <div class="p-6 space-y-2">
                    <div><span class="text-xs text-gray-500 block">Nom</span><span class="text-sm font-medium">{{ $demande->prestataire_nom }}</span></div>
                    @if($demande->numero_commande)
                    <div><span class="text-xs text-gray-500 block">N° commande</span><span class="text-sm font-medium">{{ $demande->numero_commande }}</span></div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

    @if(auth()->user()->hasRole('approbateur') 
        && $demande->approbateur_n1_id === auth()->id() 
        && strtolower($demande->statut) === 'en_attente')
        <div class="mt-8">
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Décision de l'approbateur</h2>
                    <p class="mt-1 text-sm text-gray-500">Vous pouvez approuver ou rejeter cette demande.</p>
                </div>
                <div class="p-6 space-y-6">
                    <form action="{{ route('demande.updateStatus', $demande->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="status" value="approuve">
                        <div>
                            <label for="commentaire_approbation" class="label">
                                Commentaire (optionnel en cas d'approbation)
                            </label>
                            <textarea
                                id="commentaire_approbation"
                                name="commentaire"
                                rows="3"
                                class="input-senelec"
                                placeholder="Ajoutez un commentaire pour le demandeur (optionnel)..."
                            >{{ old('commentaire') }}</textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('demandes.aapprouver') }}" class="btn-secondary">
                                Retour à la liste
                            </a>
                            <button type="submit" class="btn-success inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approuver la demande
                            </button>
                        </div>
                    </form>

                    <div class="border-t border-gray-100 pt-6">
                        <form action="{{ route('demande.updateStatus', $demande->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="status" value="rejete">
                            <div>
                                <label for="motif_rejet" class="label">
                                    Motif du rejet <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="motif_rejet"
                                    name="motif"
                                    rows="3"
                                    class="input-senelec"
                                    placeholder="Indiquez clairement le motif du rejet..."
                                    required
                                >{{ old('motif') }}</textarea>
                            </div>
                            <div class="flex items-center justify-end">
                                <button type="submit" class="btn-danger inline-flex items-center gap-2" onclick="return confirm('Confirmez-vous le rejet de cette demande ?');">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Rejeter la demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $serviceRole = \App\Helpers\ServiceRedirectionHelper::getServiceRoleForNature($demande->nature, $demande->unite_code);
        $uniteInfo = \App\Helpers\ServiceRedirectionHelper::getUniteFromNature($demande->nature, $demande->unite_code);
        $servicesStructure = config('services_structure.services_structure');
        $availableUnits = [];

        foreach ($servicesStructure as $serviceCode => $service) {
            foreach ($service['unites'] as $code => $unite) {
                $availableUnits[] = [
                    'service' => $serviceCode,
                    'code' => $code,
                    'name' => $unite['name'],
                ];
            }
        }
    @endphp

    @if($serviceRole && auth()->user()->hasRole($serviceRole) && strtolower($demande->statut) === 'accepte')
        <div class="mt-8">
            <div class="card-senelec">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Traitement par le service {{ strtoupper($serviceRole) }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Vous pouvez imputer cette demande vers l'unité appropriée ou la rejeter.
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <form action="{{ route('demande.dispatch', $demande) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" value="dispatch">

                        @if($uniteInfo)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 space-y-3">
                                <p class="text-sm font-semibold text-emerald-800 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Imputer et dispatcher
                                </p>
                                <div class="rounded-lg bg-emerald-100/70 px-3 py-2 text-xs text-emerald-900 space-y-1">
                                    <p><span class="font-semibold">Nature :</span> {{ $demande->nature ?? '-' }}</p>
                                    <p><span class="font-semibold">Unité :</span> {{ $uniteInfo['code'] ?? '-' }}</p>
                                    <p><span class="font-semibold">Sera dispatchée vers :</span> {{ $uniteInfo['name'] ?? '-' }}</p>
                                </div>

                                @if($serviceRole === 'seg' && ($uniteInfo['code'] ?? null) === 'UMR')
                                    {{-- Bloc spécifique UMR : période obligatoire avant confirmation --}}
                                    <div class="mt-3 space-y-3">
                                        <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-xs flex gap-2">
                                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 3a9 9 0 019 9 9 9 0 01-9 9 9 9 0 01-9-9 9 9 0 019-9z"/>
                                            </svg>
                                            <p class="text-amber-800">
                                                <span class="font-semibold">Important :</span>
                                                Cette demande UMR nécessite une période d'intervention.
                                                Merci de saisir une <span class="font-semibold">date début</span> et une
                                                <span class="font-semibold">date fin</span> avant de confirmer l'imputation.
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="date_debut_intervention" class="block text-xs font-semibold text-gray-700 mb-1">
                                                    Date début (date & heure) *
                                                </label>
                                                <input
                                                    type="datetime-local"
                                                    id="date_debut_intervention"
                                                    name="date_debut_intervention"
                                                    class="input-senelec text-sm"
                                                    value="{{ old('date_debut_intervention', optional($demande->date_debut_intervention)->format('Y-m-d\\TH:i')) }}"
                                                    required
                                                >
                                            </div>
                                            <div>
                                                <label for="date_fin_intervention" class="block text-xs font-semibold text-gray-700 mb-1">
                                                    Date fin (date & heure) *
                                                </label>
                                                <input
                                                    type="datetime-local"
                                                    id="date_fin_intervention"
                                                    name="date_fin_intervention"
                                                    class="input-senelec text-sm"
                                                    value="{{ old('date_fin_intervention', optional($demande->date_fin_intervention)->format('Y-m-d\\TH:i')) }}"
                                                    required
                                                >
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(count($availableUnits) > 1)
                                    <div class="mt-3">
                                        <label for="unite_full_override" class="label text-xs">
                                            Corriger le service / l'unité si nécessaire
                                        </label>
                                        <select id="unite_full_override"
                                                name="unite_full_override"
                                                class="input-senelec text-xs py-1.5">
                                            @foreach($availableUnits as $unit)
                                                <option value="{{ $unit['service'] . ':' . $unit['code'] }}"
                                                    @if($uniteInfo && $uniteInfo['code'] === $unit['code'] && $uniteInfo['service'] === $unit['service']) selected @endif>
                                                    {{ $unit['service'] }} - {{ $unit['code'] }} - {{ $unit['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-[11px] text-emerald-700">
                                            Si le demandeur s'est trompé de service ou d'unité (ex : SA au lieu de SEG),
                                            choisissez ici le bon service / la bonne unité avant de confirmer l'imputation.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3">
                            <button type="submit" class="btn-success inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                </svg>
                                Imputer / dispatcher vers l'unité
                            </button>
                        </div>
                    </form>

                    <div class="border-t border-gray-100 pt-6">
                        <form action="{{ route('demande.dispatch', $demande) }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <div>
                                <label for="motif_rejet_sa_seg" class="label">
                                    Motif du rejet vers le demandeur <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="motif_rejet_sa_seg"
                                    name="rejection_reason"
                                    rows="3"
                                    class="input-senelec"
                                    placeholder="Expliquez clairement pourquoi la demande est rejetée..."
                                    required
                                >{{ old('rejection_reason') }}</textarea>
                            </div>
                            <div class="flex items-center justify-end">
                                <button type="submit" class="btn-danger inline-flex items-center gap-2" onclick="return confirm('Confirmez-vous le rejet de cette demande vers le demandeur ?');">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Rejeter la demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
