@extends('layouts.app')

@section('title', 'Mes demandes')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes demandes de travaux</h1>
            <p class="mt-1 text-gray-500">
                {{ count($demandes) }} demande(s)
                @if(!empty($statutFilter))
                    · Statut filtré :
                    <span class="font-semibold">
                        {{ ucfirst(str_replace('_', ' ', $statutFilter)) }}
                    </span>
                @endif
                @if(!empty($teamTypeFilter))
                    · Type :
                    <span class="font-semibold">
                        {{ $teamTypeFilter === 'interne' ? 'Travaux internes' : 'Travaux externes' }}
                    </span>
                @endif
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3">
            
            <a href="{{ route('demande.create') }}" class="btn-senelec">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle demande
            </a>
        </div>
    </div>

    {{-- Cards de filtrage par statut --}}
    @php
        $statusCards = [
            'brouillon'  => ['label' => 'Brouillon',  'class' => 'stat-card-gray'],
            'en_attente' => ['label' => 'En attente', 'class' => 'stat-card-orange'],
            'accepte'    => ['label' => 'Acceptées',  'class' => 'stat-card-teal'],
            'impute'     => ['label' => 'Imputées',   'class' => 'stat-card-magenta'],
            'valide'     => ['label' => 'Validées',   'class' => 'stat-card-green'],
            'en_cours'   => ['label' => 'En cours',   'class' => 'stat-card-blue'],
            'rejete'     => ['label' => 'Rejetées',   'class' => 'stat-card-red'],
            'termine'    => ['label' => 'Terminées',  'class' => 'stat-card-emerald'],
            'cloture'    => ['label' => 'Clôturées',  'class' => 'stat-card-purple'],
        ];
    @endphp

    {{-- Filtres complémentaires --}}
    <div class="card-senelec p-4">
        <form method="GET" action="{{ route('demande.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="team_type" class="block text-sm font-semibold text-gray-700 mb-1">Type de travaux</label>
                <select name="team_type" id="team_type" class="input-senelec">
                    <option value="">Tous les types</option>
                    <option value="interne" {{ ($teamTypeFilter ?? '') === 'interne' ? 'selected' : '' }}>Travaux internes</option>
                    <option value="externe" {{ ($teamTypeFilter ?? '') === 'externe' ? 'selected' : '' }}>Travaux externes</option>
                </select>
            </div>
            <input type="hidden" name="statut" value="{{ $statutFilter }}">
            <div class="flex gap-2">
                <button type="submit" class="btn-senelec flex-1">Filtrer</button>
                <a href="{{ route('demande.index') }}" class="btn-secondary px-4">Réinitialiser</a>
            </div>
        </form>
    </div>

    {{-- 2 lignes de cards (5 colonnes en desktop : 1 "Tout" + 9 statuts) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Card "Tout" pour réinitialiser --}}
        @php
            $isAllActive = empty($statutFilter);
            $allClass = 'stat-card-gray' . ($isAllActive ? ' ring-2 ring-senelec-purple' : '');
        @endphp
        <a href="{{ route('demande.index', array_filter(['team_type' => $teamTypeFilter])) }}" class="block">
            <div class="{{ $allClass }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Tout</p>
                        <p class="stat-value">{{ $totalDemandes ?? count($demandes) }}</p>
                    </div>
                </div>
            </div>
        </a>
        @foreach($statusCards as $key => $meta)
            @php
                $count = $statsParStatut[$key] ?? 0;
                $isActive = !empty($statutFilter) && $statutFilter === $key;
                $cardClass = $meta['class'] . ($isActive ? ' ring-2 ring-senelec-purple' : '');
            @endphp
            <a href="{{ route('demande.index', array_filter(['statut' => $key, 'team_type' => $teamTypeFilter])) }}" class="block">
                <div class="{{ $cardClass }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="stat-label">{{ $meta['label'] }}</p>
                            <p class="stat-value">{{ $count }}</p>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <x-demandes-table 
        :demandes="$demandes" 
        :show-user="false" 
        :show-rejection-reason="true"
        edit-route="demande.edit" 
    />
</div>
@endsection
