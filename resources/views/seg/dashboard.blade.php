@extends('layouts.app')

@section('title', 'Tableau de bord SEG')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord SEG</h1>
        <p class="mt-1 text-gray-500">Suivi des demandes SEG (Service Électricité Générale)</p>
    </div>

    {{-- Stats globales --}}
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

    {{-- Répartition par statut --}}
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
                    <canvas id="segDonut"></canvas>
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

    {{-- Évolution 12 mois --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Évolution sur 12 mois</h2>
        </div>
        <div class="p-6">
            <div class="w-full overflow-x-auto">
                <div class="min-w-[700px]">
                    <canvas id="segLine" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Travaux en cours --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Travaux en cours</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[900px]">
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Demandeur</th>
                        <th>Site</th>
                        <th>Unité</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($travauxDebutes as $demande)
                    <tr class="cursor-pointer hover:bg-gray-50 transition-colors" onclick="window.location='{{ route('demande.show', $demande) }}'">
                        <td><span class="font-mono text-sm font-semibold text-senelec-purple">{{ $demande->numero_demande }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                @php
                                    $photoUrl = $demande->user->photo_url ?? null;
                                @endphp
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="{{ $demande->user->name ?? 'Demandeur' }}"
                                         class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($demande->user->name ?? '', 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $demande->user->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm">{{ $demande->site->libelle ?? '-' }}</td>
                        <td class="text-sm">
                            @if($demande->umr) UMR - {{ $demande->umr->name }}
                            @elseif($demande->utgc) UTGC - {{ $demande->utgc->name }}
                            @else -
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">{{ $demande->date_debut_intervention ? $demande->date_debut_intervention->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-sm text-gray-500">{{ $demande->date_fin_intervention ? $demande->date_fin_intervention->format('d/m/Y H:i') : '-' }}</td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucun travail en cours</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Périodes à venir --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Périodes à venir</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[900px]">
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Demandeur</th>
                        <th>Site</th>
                        <th>Unité</th>
                        <th>Période</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodesAvenir as $demande)
                    <tr class="cursor-pointer hover:bg-gray-50 transition-colors" onclick="window.location='{{ route('demande.show', $demande) }}'">
                        <td><span class="font-mono text-sm font-semibold text-senelec-purple">{{ $demande->numero_demande }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                @php
                                    $photoUrl = $demande->user->photo_url ?? null;
                                @endphp
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="{{ $demande->user->name ?? 'Demandeur' }}"
                                         class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($demande->user->name ?? '', 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $demande->user->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm">{{ $demande->site->libelle ?? '-' }}</td>
                        <td class="text-sm">
                            @if($demande->umr) UMR - {{ $demande->umr->name }}
                            @elseif($demande->utgc) UTGC - {{ $demande->utgc->name }}
                            @else -
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">
                            {{ $demande->date_debut_intervention ? $demande->date_debut_intervention->format('d/m/Y H:i') : '?' }}
                            -
                            {{ $demande->date_fin_intervention ? $demande->date_fin_intervention->format('d/m/Y H:i') : '?' }}
                        </td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">Aucune période à venir</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('seg.demandes.approuvees') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm text-gray-600">Demandes approuvées SEG</span>
            </a>
            <a href="{{ route('seg.demandes.imputees') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                <span class="text-sm text-gray-600">Demandes imputées</span>
            </a>
            <a href="{{ route('seg.periodes_attente') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm text-gray-600">Périodes en attente</span>
            </a>
            <a href="{{ route('seg.demandes') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="text-sm text-gray-600">Toutes les demandes</span>
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

        const donutCtx = document.getElementById('segDonut')?.getContext('2d');
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

        const lineCtx = document.getElementById('segLine')?.getContext('2d');
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
