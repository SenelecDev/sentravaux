@extends('layouts.app')

@section('title', 'Tableau de bord SAD')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord SAD</h1>
        <p class="mt-1 text-gray-500">Suivi des demandes SA (Service Administratif)</p>
    </div>

    <form method="GET" class="card-senelec p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Unite</label>
            <select name="unite" class="select-senelec w-full text-sm">
                <option value="tous">Toutes</option>
                @foreach($unites as $code => $nom)
                    <option value="{{ $code }}" {{ request('unite') === $code ? 'selected' : '' }}>{{ $code }} - {{ $nom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Demandes (statut)</label>
            <select name="demande" class="select-senelec w-full text-sm">
                <option value="tous">Tous</option>
                @foreach(['brouillon','en_attente','accepte','impute','valide','en_cours','termine','cloture','rejete'] as $s)
                    <option value="{{ $s }}" {{ request('demande') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Type de travaux</label>
            <select name="team_type" class="select-senelec w-full text-sm">
                <option value="tous">Tous</option>
                <option value="interne" {{ request('team_type') === 'interne' ? 'selected' : '' }}>Interne</option>
                <option value="externe" {{ request('team_type') === 'externe' ? 'selected' : '' }}>Externe</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Periode</label>
            <select name="periode" class="select-senelec w-full text-sm">
                <option value="tous" {{ request('periode', 'tous') === 'tous' ? 'selected' : '' }}>Toutes</option>
                <option value="semaine" {{ request('periode') === 'semaine' ? 'selected' : '' }}>Semaine</option>
                <option value="mois" {{ request('periode') === 'mois' ? 'selected' : '' }}>Mois</option>
                <option value="annee" {{ request('periode') === 'annee' ? 'selected' : '' }}>Annee</option>
                <option value="custom" {{ request('periode') === 'custom' ? 'selected' : '' }}>Personnalisee</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Nature</label>
            <select name="nature" class="select-senelec w-full text-sm">
                <option value="tous">Toutes</option>
                @foreach($natures as $nature)
                    <option value="{{ $nature }}" {{ request('nature') === $nature ? 'selected' : '' }}>{{ $nature }}</option>
                @endforeach
            </select>
        </div>

        <div><label class="block text-xs text-gray-500 mb-1">Date debut min</label><input type="date" name="date_debut_min" value="{{ request('date_debut_min') }}" class="input-senelec w-full text-sm"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Date debut max</label><input type="date" name="date_debut_max" value="{{ request('date_debut_max') }}" class="input-senelec w-full text-sm"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Date fin min</label><input type="date" name="date_fin_min" value="{{ request('date_fin_min') }}" class="input-senelec w-full text-sm"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Date fin max</label><input type="date" name="date_fin_max" value="{{ request('date_fin_max') }}" class="input-senelec w-full text-sm"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Creation min</label><input type="date" name="periode_min" value="{{ request('periode_min') }}" class="input-senelec w-full text-sm"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Creation max</label><input type="date" name="periode_max" value="{{ request('periode_max') }}" class="input-senelec w-full text-sm"></div>
        <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">Recherche</label><input type="text" name="search" value="{{ request('search') }}" class="input-senelec w-full text-sm" placeholder="N demande ou objet"></div>
        <div class="md:col-span-4 flex gap-2">
            <button type="submit" class="btn-primary text-sm">Filtrer</button>
            <a href="{{ route('sad.dashboard') }}" class="btn-secondary text-sm">Reinitialiser</a>
            <a href="{{ route('sad.dashboard.export', request()->query()) }}" class="btn-warning text-sm">Exporter</a>
        </div>
    </form>

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
                    <canvas id="sadDonut"></canvas>
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
            <div class="w-full h-72">
                <canvas id="sadLine"></canvas>
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
                            @if($demande->umt) UMT - {{ $demande->umt->name }}
                            @elseif($demande->ubt) UBT - {{ $demande->ubt->name }}
                            @elseif($demande->unsp) UNSP - {{ $demande->unsp->name }}
                            @else -
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">{{ $demande->date_debut_intervention ? \Carbon\Carbon::parse($demande->date_debut_intervention)->format('d/m/Y') : '-' }}</td>
                        <td class="text-sm text-gray-500">{{ $demande->date_fin_intervention ? \Carbon\Carbon::parse($demande->date_fin_intervention)->format('d/m/Y') : '-' }}</td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucun travail en cours</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Demandes filtrees</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[900px]">
                <thead><tr><th>N°</th><th>Statut</th><th>Unite</th><th>Nature</th><th>Objet</th><th>Debut</th><th>Fin</th><th>Date de clôture</th></tr></thead>
                <tbody>
                    @forelse($demandesFiltrees as $demande)
                        <tr class="cursor-pointer hover:bg-gray-50" onclick="window.location='{{ route('demande.show', $demande) }}'">
                            <td>{{ $demande->numero_demande }}</td>
                            <td>{{ ucfirst(str_replace('_',' ', $demande->statut)) }}</td>
                            <td>{{ $demande->unite_code ?? '-' }}</td>
                            <td>{{ $demande->nature ?? '-' }}</td>
                            <td class="max-w-[320px] truncate">{{ $demande->objet }}</td>
                            <td>{{ $demande->date_debut_intervention?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $demande->date_fin_intervention?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                @php
                                    $isCloture = str_starts_with(strtolower((string) ($demande->statut ?? '')), 'clotur');
                                    $finTravauxCloture = $demande->date_cloture
                                        ?? $demande->date_fin_intervention
                                        ?? $demande->date_fin
                                        ?? $demande->updated_at;
                                @endphp
                                {{ $isCloture ? optional($finTravauxCloture)->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-8 text-gray-500">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $demandesFiltrees->links() }}</div>
    </div>

    {{-- Actions rapides --}}
    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('sad.demandes.approuvees') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm text-gray-600">Demandes approuvées</span>
            </a>
            <a href="{{ route('sad.demandes.imputees') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                <span class="text-sm text-gray-600">Demandes imputées</span>
            </a>
            <a href="{{ route('sad.demandes') }}" class="quick-action">
                <svg class="quick-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="text-sm text-gray-600">Toutes les demandes</span>
            </a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    (function () {
        function renderCharts() {
        if (typeof Chart === 'undefined') return;
        const statutKeys = @json(array_keys($demandesParMois));
        const statutLabels = statutKeys.map(function (s) {
            return s.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        });
        const statutValues = @json(array_values($demandesParMois));

        const donutCtx = document.getElementById('sadDonut')?.getContext('2d');
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
                        borderWidth: 0,
                        hoverOffset: 0
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

        const lineCtx = document.getElementById('sadLine')?.getContext('2d');
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
                        pointBackgroundColor: '#4C1D95',
                        pointHoverRadius: 3,
                        pointHoverBorderWidth: 0
                    }]
                },
                options: {
                    animation: false,
                    events: [],
                    responsive: true,
                    maintainAspectRatio: false,
                    elements: {
                        point: {
                            radius: 3,
                            hoverRadius: 3,
                            borderWidth: 0,
                            hoverBorderWidth: 0
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        }
        }

        if (typeof Chart === 'undefined') {
            const fallback = document.createElement('script');
            fallback.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';
            fallback.onload = renderCharts;
            document.head.appendChild(fallback);
            return;
        }

        renderCharts();
    })();
</script>
@endpush
