@extends('layouts.app')

@section('title', 'Tableau de bord DAGE')

@push('styles')
<style>
    .filter-badge { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ showFilters: true }">
    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord DAGE</h1>
            <p class="mt-1 text-gray-500">Vue d'ensemble de toutes les demandes de travaux</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showFilters = !showFilters" class="btn-senelec-outline text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filtres
            </button>
            <a href="{{ route('dage.export', request()->query()) }}" class="btn-success text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exporter Excel
            </a>
        </div>
    </div>

    {{-- Stats par statut --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9 gap-3">
        @php
            $statutConfig = [
                'brouillon' => ['label' => 'Brouillon', 'class' => 'stat-card-gray'],
                'en_attente' => ['label' => 'En attente', 'class' => 'stat-card-orange'],
                'accepte' => ['label' => 'Accepté', 'class' => 'stat-card-teal'],
                'impute' => ['label' => 'Imputé', 'class' => 'stat-card-purple'],
                'valide' => ['label' => 'Validé', 'class' => 'stat-card-blue'],
                'en_cours' => ['label' => 'En cours', 'class' => 'stat-card-magenta'],
                'rejete' => ['label' => 'Rejeté', 'class' => 'stat-card-red'],
                'termine' => ['label' => 'Terminé', 'class' => 'stat-card-emerald'],
                'cloture' => ['label' => 'Clôturé', 'class' => 'stat-card-green'],
            ];
        @endphp
        @foreach($statutConfig as $statut => $config)
        <a href="{{ route('dage.dashboard', array_merge(request()->query(), ['statut' => $statut])) }}"
           class="{{ $config['class'] }} {{ ($statutFilter ?? '') === $statut ? 'ring-2 ring-offset-2 ring-gray-400' : '' }}">
            <div class="stat-value text-lg">{{ $statsParStatut[$statut] ?? 0 }}</div>
            <div class="stat-label text-[10px]">{{ $config['label'] }}</div>
        </a>
        @endforeach
    </div>

    {{-- Total --}}
    <div class="rounded-2xl shadow-lg px-6 py-4 flex flex-col items-center justify-center text-white"
         style="background-color: var(--color-senelec-purple);">
        <div class="text-3xl font-extrabold leading-tight">{{ $totalDemandes }}</div>
        <div class="text-[11px] uppercase tracking-[0.18em] opacity-90 mt-1">Total des demandes (filtré)</div>
        @if($statutFilter && $statutFilter !== 'tous')
            <a href="{{ route('dage.dashboard', array_merge(request()->except('statut'))) }}"
               class="mt-1 text-[10px] underline text-white/90 hover:text-white">
                Réinitialiser le filtre
            </a>
        @endif
    </div>

    {{-- Filtres avancés --}}
    <div x-show="showFilters" x-transition class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Filtres avancés</h2>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('dage.dashboard') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    {{-- Recherche --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Recherche</label>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="input-senelec w-full text-sm" placeholder="N° demande, objet, demandeur...">
                    </div>

                    {{-- Statut --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                        <select name="statut" class="select-senelec w-full text-sm">
                            <option value="tous">Tous les statuts</option>
                            @foreach($statutConfig as $statut => $config)
                                <option value="{{ $statut }}" {{ ($statutFilter ?? '') === $statut ? 'selected' : '' }}>{{ $config['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Année --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Année</label>
                        <select name="annee" class="select-senelec w-full text-sm">
                            @foreach($annees as $year)
                                <option value="{{ $year }}" {{ ($anneeFilter ?? '') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mois --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Mois</label>
                        <select name="mois" class="select-senelec w-full text-sm">
                            <option value="">Tous les mois</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ ($moisFilter ?? '') == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Semaine --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Semaine</label>
                        <select name="semaine" class="select-senelec w-full text-sm">
                            <option value="">Toutes les semaines</option>
                            @for($w = 1; $w <= 53; $w++)
                                <option value="{{ $w }}" {{ ($semaineFilter ?? '') == $w ? 'selected' : '' }}>Semaine {{ $w }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Service (structure) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Service (structure)</label>
                        <select name="service" class="select-senelec w-full text-sm">
                            <option value="tous">Tous</option>
                            @foreach($services as $code => $service)
                                <option value="{{ $code }}" {{ ($serviceFilter ?? '') === $code ? 'selected' : '' }}>{{ $service['nom'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Service demandeur --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Service demandeur</label>
                        <select name="service_demandeur" class="select-senelec w-full text-sm">
                            <option value="tous">Tous</option>
                            @foreach($servicesDemandeurs as $srv)
                                <option value="{{ $srv->id }}" {{ ($serviceDemandeurFilter ?? '') == $srv->id ? 'selected' : '' }}>{{ $srv->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Site --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Site</label>
                        <select name="site" class="select-senelec w-full text-sm">
                            <option value="tous">Tous les sites</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ ($siteFilter ?? '') == $site->id ? 'selected' : '' }}>{{ $site->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unité (UAG, UPNS, UGBT, UTGC, UMR) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Unité</label>
                        <select name="unite" class="select-senelec w-full text-sm">
                            <option value="tous">Toutes les unités</option>
                            @foreach($unites as $code => $nom)
                                <option value="{{ $code }}" {{ ($uniteFilter ?? '') === $code ? 'selected' : '' }}>{{ $code }} - {{ $nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nature --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nature</label>
                        <select name="nature" class="select-senelec w-full text-sm">
                            <option value="tous">Toutes les natures</option>
                            @foreach($natures as $nature)
                                <option value="{{ $nature }}" {{ ($natureFilter ?? '') === $nature ? 'selected' : '' }}>{{ $nature }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Par page --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Par page</label>
                        <select name="per_page" class="select-senelec w-full text-sm">
                            @foreach([10, 25, 50, 100] as $pp)
                                <option value="{{ $pp }}" {{ ($perPage ?? 25) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-4">
                    <button type="submit" class="btn-senelec text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Appliquer
                    </button>
                    <a href="{{ route('dage.dashboard') }}" class="btn-secondary text-sm">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Donut répartition par statut --}}
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Répartition par statut</h2>
            </div>
            <div class="p-6 grid grid-cols-1 lg:grid-cols-[260px,1fr] gap-8 items-center">
                <div class="flex items-center justify-center">
                    <div class="w-64 h-64">
                        <canvas id="dageDonut"></canvas>
                    </div>
                </div>
                <div class="space-y-2">
                    @php
                        $labelsDonut = collect($repartitionStatuts)->pluck('statut')->toArray();
                        $valuesDonut = collect($repartitionStatuts)->pluck('count')->toArray();
                    @endphp
                    @foreach($repartitionStatuts as $item)
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-senelec-purple/70"></span>
                                <span class="text-gray-700">{{ $item['statut'] }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Évolution mensuelle (graphique + tableau) --}}
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Évolution mensuelle (6 mois)</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="w-full h-56">
                    <canvas id="dageLine"></canvas>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-senelec text-xs">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                @foreach(['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'] as $s)
                                    <th class="text-center">{{ Str::limit($statutConfig[$s]['label'] ?? ucfirst($s), 8) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evolutionMensuelle as $row)
                            <tr>
                                <td class="font-medium">{{ $row['mois'] }}</td>
                                @foreach(['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'] as $s)
                                    <td class="text-center">
                                        <span class="{{ ($row[$s] ?? 0) > 0 ? 'font-semibold text-gray-900' : 'text-gray-400' }}">{{ $row[$s] ?? 0 }}</span>
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Travaux en cours & périodes validées --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Travaux en cours</h2>
                <span class="text-xs text-gray-500">{{ $travauxEnCours->count() }} derniers</span>
            </div>
            <div class="p-6 space-y-3">
                @forelse($travauxEnCours as $demandeEnCours)
                    <a href="{{ route('demande.show', $demandeEnCours) }}" class="block p-3 rounded-lg border border-gray-100 hover:border-senelec-purple/40 hover:bg-purple-50/50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $demandeEnCours->numero_demande }}</p>
                                <p class="text-xs text-gray-600 line-clamp-1">{{ $demandeEnCours->objet }}</p>
                                <p class="text-[11px] text-gray-400 mt-1">
                                    {{ $demandeEnCours->user->name ?? '-' }} • {{ $demandeEnCours->service->libelle ?? '-' }} • {{ $demandeEnCours->site->libelle ?? '-' }}
                                </p>
                            </div>
                            <span class="text-[11px] text-gray-500">{{ $demandeEnCours->date_debut_intervention?->format('d/m H:i') }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Aucun travaux en cours.</p>
                @endforelse
            </div>
        </div>

        <div class="card-senelec">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Périodes validées</h2>
                <span class="text-xs text-gray-500">{{ $periodesValidees->count() }} dernières</span>
            </div>
            <div class="p-6 space-y-3">
                @forelse($periodesValidees as $demandePeriode)
                    <a href="{{ route('demande.show', $demandePeriode) }}" class="block p-3 rounded-lg border border-gray-100 hover:border-senelec-purple/40 hover:bg-purple-50/50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $demandePeriode->numero_demande }}</p>
                                <p class="text-xs text-gray-600 line-clamp-1">{{ $demandePeriode->objet }}</p>
                                <p class="text-[11px] text-gray-400 mt-1">
                                    {{ $demandePeriode->user->name ?? '-' }} • {{ $demandePeriode->service->libelle ?? '-' }} • {{ $demandePeriode->site->libelle ?? '-' }}
                                </p>
                            </div>
                            <span class="text-[11px] text-gray-500">
                                {{ $demandePeriode->date_debut_intervention?->format('d/m') }}
                                -
                                {{ $demandePeriode->date_fin_intervention?->format('d/m') }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Aucune période validée récemment.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tableau des demandes --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Demandes ({{ $demandes->total() }} résultat(s))</h2>
                <span class="text-sm text-gray-500">Page {{ $demandes->currentPage() }} / {{ $demandes->lastPage() }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table-senelec text-sm">
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Demandeur</th>
                        <th>Objet</th>
                        <th>Nature</th>
                        <th>Site</th>
                        <th>Service</th>
                        <th>Statut</th>
                        <th>Approbateur</th>
                        <th>Affectation</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr>
                        <td>
                            <a href="{{ route('demande.show', $demande) }}" class="font-medium text-[#e30613] hover:underline">
                                {{ $demande->numero_demande }}
                            </a>
                        </td>
                        <td>{{ $demande->user->name ?? '-' }}</td>
                        <td class="max-w-[200px] truncate" title="{{ $demande->objet }}">{{ Str::limit($demande->objet, 40) }}</td>
                        <td><span class="text-xs">{{ $demande->nature ?? '-' }}</span></td>
                        <td>{{ $demande->site->libelle ?? '-' }}</td>
                        <td>{{ $demande->service->libelle ?? '-' }}</td>
                        <td><x-status-badge :statut="$demande->statut" /></td>
                        <td>{{ $demande->approvedBy->name ?? ($demande->approbateurN1->name ?? '-') }}</td>
                        <td>
                            @php
                                $affectations = [];
                                if($demande->sad) $affectations[] = 'SAD: ' . $demande->sad->name;
                                if($demande->seg) $affectations[] = 'SEG: ' . $demande->seg->name;
                                foreach(['umt', 'ubt', 'unsp', 'umr', 'utgc'] as $u) {
                                    if($demande->$u) $affectations[] = strtoupper($u) . ': ' . $demande->$u->name;
                                }
                            @endphp
                            @if(count($affectations) > 0)
                                <span class="text-xs text-gray-600">{{ implode(', ', $affectations) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-500">{{ $demande->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-gray-500 py-8">Aucune demande trouvée avec ces critères.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($demandes->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $demandes->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function () {
        // Donut répartition par statut
        const donutCtx = document.getElementById('dageDonut')?.getContext('2d');
        if (donutCtx) {
            const labels = @json(collect($repartitionStatuts)->pluck('statut'));
            const values = @json(collect($repartitionStatuts)->pluck('count'));

            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#4C1D95', '#7C3AED', '#A855F7', '#EC4899',
                            '#F97316', '#10B981', '#0EA5E9', '#6366F1', '#F43F5E'
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

        // Ligne évolution mensuelle (total demandes par mois)
        const lineCtx = document.getElementById('dageLine')?.getContext('2d');
        if (lineCtx) {
            const evo = @json($evolutionMensuelle);
            const moisLabels = evo.map(row => row.mois);
            const totalParMois = evo.map(row => {
                return (
                    (row.brouillon ?? 0) +
                    (row.en_attente ?? 0) +
                    (row.en_cours ?? 0) +
                    (row.accepte ?? 0) +
                    (row.rejete ?? 0) +
                    (row.valide ?? 0) +
                    (row.impute ?? 0) +
                    (row.termine ?? 0) +
                    (row.cloture ?? 0)
                );
            });

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
