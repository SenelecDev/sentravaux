@extends('layouts.app')

@section('title', 'Tableau de bord SGB')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord SGB</h1>
        <p class="mt-1 text-gray-500">Suivi des demandes SGB (Service Gestions Budget)</p>
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
            <a href="{{ route('sgb.dashboard') }}" class="btn-secondary text-sm">Reinitialiser</a>
            <a href="{{ route('sgb.dashboard.export', request()->query()) }}" class="btn-warning text-sm">Exporter</a>
        </div>
    </form>

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
                <div class="w-64 h-64"><canvas id="sgbDonut"></canvas></div>
            </div>
            <div class="lg:col-span-2">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($demandesParMois as $statut => $count)
                        <div class="p-3 rounded-lg bg-gray-100 text-gray-700">
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
        <div class="p-6 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Évolution sur 12 mois</h2></div>
        <div class="p-6"><div class="w-full h-72"><canvas id="sgbLine"></canvas></div></div>
    </div>

    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Demandes filtrees</h2></div>
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
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function renderCharts() {
            if (typeof Chart === 'undefined') return;
            const statutKeys = @json(array_keys($demandesParMois));
            const statutValues = @json(array_values($demandesParMois));
            const labels = statutKeys.map(s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));

            const donutCtx = document.getElementById('sgbDonut')?.getContext('2d');
            if (donutCtx) {
                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: { labels, datasets: [{ data: statutValues, borderWidth: 0, hoverOffset: 0 }] },
                    options: { plugins: { legend: { display: false } }, cutout: '65%' }
                });
            }

            const lineCtx = document.getElementById('sgbLine')?.getContext('2d');
            if (lineCtx) {
                const moisLabels = @json(array_map(function ($d) { return $d['mois']; }, $derniersDouzeData));
                const totalParMois = @json(array_map(function ($d) { return array_sum($d['data']); }, $derniersDouzeData));
                new Chart(lineCtx, {
                    type: 'line',
                    data: { labels: moisLabels, datasets: [{ label: 'Total demandes', data: totalParMois, borderColor: '#4C1D95', backgroundColor: 'rgba(76, 29, 149, 0.15)', tension: 0.3, fill: true, pointRadius: 3 }] },
                    options: { animation: false, events: [], responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
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

