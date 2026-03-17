@extends('layouts.app')

@section('title', 'Tableau de bord Approbateur')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Approbateur</h1>
        <p class="mt-1 text-gray-500">Suivi des demandes à approuver</p>
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
                </form>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            <div class="flex items-center justify-center">
                <div class="w-64 h-64">
                    <canvas id="approbateurDonut"></canvas>
                </div>
            </div>

            <div class="lg:col-span-2">
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
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
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
    </div>

    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Évolution sur 12 mois</h2>
        </div>
        <div class="p-6">
            <div class="w-full overflow-x-auto">
                <div class="min-w-[700px]">
                    <canvas id="approbateurLine" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('demandes.aapprouver') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm text-gray-600">À approuver</span>
            </a>
            <a href="{{ route('demande.demandes_approuvees') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm text-gray-600">Approuvées</span>
            </a>
            <a href="{{ route('demande.demandes_rejetes') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="text-sm text-gray-600">Rejetées</span>
            </a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const statutKeys = @json(array_keys($demandesParMois));
        const statutLabels = statutKeys.map(function (s) {
            return s.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        });
        const statutValues = @json(array_values($demandesParMois));

        const donutCtx = document.getElementById('approbateurDonut')?.getContext('2d');
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: statutLabels,
                    datasets: [{
                        data: statutValues,
                        backgroundColor: [
                            '#9CA3AF', '#FBBF24', '#3B82F6', '#A855F7',
                            '#10B981', '#6366F1', '#EF4444', '#14B8A6', '#10B981'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: {
                        legend: { display: false }
                    },
                    cutout: '65%'
                }
            });
        }

        const lineCtx = document.getElementById('approbateurLine')?.getContext('2d');
        if (lineCtx) {
            const moisLabels = @json(array_map(function ($d) { return $d['mois']; }, $derniersDouzeData));
            const totalParMois = @json(array_map(function ($d) { return array_sum($d['data']); }, $derniersDouzeData));

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: moisLabels,
                    datasets: [{
                        label: 'Total demandes',
                        data: totalParMois,
                        borderColor: '#4C1D95',
                        backgroundColor: 'rgba(76, 29, 149, 0.15)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#4C1D95'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    })();
</script>
@endpush
