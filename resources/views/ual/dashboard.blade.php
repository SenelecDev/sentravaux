@extends('layouts.app')

@section('title', 'Tableau de bord UAL')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord UAL</h1>
        <p class="mt-1 text-gray-500">Suivi des demandes UAL</p>
    </div>

    <x-dashboard-stats
        :total-demandes="$totalDemandes"
        :demandes-brouillon="$demandesBrouillon"
        :demandes-en-attente="$demandesEnAttente"
        :demandes-acceptees="$demandesAcceptees"
        :demandes-imputees="$demandesImputees"
        :demandes-valides="$demandesValides"
        :demandes-en-cours="$demandesEnCours"
        :demandes-rejetees="$demandesRejetees"
        :demandes-terminees="$demandesTerminees"
        :demandes-cloturees="$demandesCloturees"
    />

    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Répartition par statut</h2>
                <form method="GET" class="flex items-center gap-2">
                    <select name="mois" class="input-senelec text-sm py-1.5" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $mois == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="annee" class="input-senelec text-sm py-1.5" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <select name="team_type" class="input-senelec text-sm py-1.5" onchange="this.form.submit()">
                        <option value="">Tous types</option>
                        <option value="interne" {{ ($teamType ?? '') === 'interne' ? 'selected' : '' }}>Interne</option>
                        <option value="externe" {{ ($teamType ?? '') === 'externe' ? 'selected' : '' }}>Externe</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="p-6">
            @php
                $colors = [
                    'brouillon' => 'bg-gray-100 text-gray-700',
                    'en_attente' => 'bg-yellow-100 text-yellow-700',
                    'accepte' => 'bg-blue-100 text-blue-700',
                    'impute' => 'bg-purple-100 text-purple-700',
                    'valide' => 'bg-green-100 text-green-700',
                    'en_cours' => 'bg-indigo-100 text-indigo-700',
                    'rejete' => 'bg-red-100 text-red-700',
                    'termine' => 'bg-teal-100 text-teal-700',
                    'cloture' => 'bg-emerald-100 text-emerald-700',
                ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($demandesParMois as $statut => $count)
                    <div class="p-3 rounded-lg {{ $colors[$statut] ?? 'bg-gray-100' }}">
                        <p class="text-xs font-medium opacity-70">{{ ucfirst(str_replace('_', ' ', $statut)) }}</p>
                        <p class="text-xl font-bold">{{ $count }}</p>
                        <p class="text-xs opacity-60">{{ $pourcentagesParStatut[$statut] ?? 0 }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="{{ route('ual.demandes.recues') }}" class="quick-action"><span class="text-sm text-gray-600">Demandes reçues</span></a>
            <a href="{{ route('ual.demandes.validees') }}" class="quick-action"><span class="text-sm text-gray-600">Validées</span></a>
            <a href="{{ route('ual.demandes.debutees') }}" class="quick-action"><span class="text-sm text-gray-600">Débutées</span></a>
            <a href="{{ route('ual.demandes.terminees') }}" class="quick-action"><span class="text-sm text-gray-600">Terminées</span></a>
            <a href="{{ route('ual.demandes.cloturees') }}" class="quick-action"><span class="text-sm text-gray-600">Clôturées</span></a>
        </div>
    </div>
</div>
@endsection

