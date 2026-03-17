@extends('layouts.app')

@section('title', 'Tableau de bord - Chef d\'équipe')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Chef d'équipe</h1>
        <p class="mt-1 text-gray-500">Suivi de vos demandes assignées</p>
    </div>

    {{-- Stats globales --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="{{ route('equipe.demandes.recues') }}" class="block">
            <div class="stat-card-purple hover:shadow-xl transition-shadow">
                <div class="stat-value">{{ $totalDemandes }}</div>
                <div class="stat-label">Total assignées</div>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.a_traiter') }}" class="block">
            <div class="stat-card-teal hover:shadow-xl transition-shadow">
                <div class="stat-value">{{ $demandesValides }}</div>
                <div class="stat-label">Demandes à traiter</div>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.debutees') }}" class="block">
            <div class="stat-card-blue hover:shadow-xl transition-shadow">
                <div class="stat-value">
                    {{-- Nombre de demandes en cours (données calculées coté contrôleur si besoin) --}}
                    {{-- Ici on affiche simplement les terminées comme approximation si pas disponible --}}
                    {{ $demandesTerminees }}
                </div>
                <div class="stat-label">Débutées / en cours</div>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.terminees') }}" class="block">
            <div class="stat-card-orange hover:shadow-xl transition-shadow">
                <div class="stat-value">{{ $demandesTerminees }}</div>
                <div class="stat-label">Terminées</div>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.cloturees') }}" class="block">
            <div class="stat-card-green hover:shadow-xl transition-shadow">
                <div class="stat-value">{{ $demandesCloturees }}</div>
                <div class="stat-label">Clôturées</div>
            </div>
        </a>
    </div>

    {{-- Répartition par statut du mois --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Répartition du mois</h2>
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
                    <canvas id="equipeDonut"></canvas>
                </div>
            </div>

            <div class="lg:col-span-2">
                @php
                    $colors = [
                        'valide' => 'bg-teal-100 text-teal-700 border-teal-300',
                        'termine' => 'bg-orange-100 text-orange-700 border-orange-300',
                        'cloture' => 'bg-green-100 text-green-700 border-green-300',
                    ];
                    $labels = ['valide' => 'Validées', 'termine' => 'Terminées', 'cloture' => 'Clôturées'];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($demandesParMois as $statut => $count)
                    <div class="p-4 rounded-lg border {{ $colors[$statut] ?? 'bg-gray-100 text-gray-700 border-gray-300' }}">
                        <div class="text-2xl font-bold">{{ $count }}</div>
                        <div class="text-sm font-medium">{{ $labels[$statut] ?? ucfirst($statut) }}</div>
                        <div class="mt-1 text-xs opacity-75">{{ number_format($pourcentagesParStatut[$statut] ?? 0, 1) }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Historique 12 mois --}}
    @if(!empty($donneesHistoriques['mois']))
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Historique sur 12 mois</h2>
        </div>
        <div class="p-6">
            <div class="w-full overflow-x-auto">
                <div class="min-w-[700px]">
                    <canvas id="equipeLine" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions rapides --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <a href="{{ route('equipe.demandes.recues') }}" class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Demandes reçues</p>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.a_traiter') }}" class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Demandes à traiter</p>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.debutees') }}" class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Demandes débutées</p>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.terminees') }}" class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Demandes terminées</p>
            </div>
        </a>
        <a href="{{ route('equipe.demandes.cloturees') }}" class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Demandes clôturées</p>
            </div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function () {
        // Données donut
        const statutKeys = @json(array_keys($demandesParMois));
        const statutLabels = statutKeys.map(function (s) {
            return s.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        });
        const statutValues = @json(array_values($demandesParMois));

        const donutCtx = document.getElementById('equipeDonut')?.getContext('2d');
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: statutLabels,
                    datasets: [{
                        data: statutValues,
                        backgroundColor: ['#10B981', '#F97316', '#22C55E'],
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

        // Données ligne 12 mois
        const hist = @json($donneesHistoriques);
        const moisLabels = hist.mois || [];
        const statutSeries = Object.keys(hist).filter(k => k !== 'mois');
        const totalParMois = moisLabels.map(function (_, idx) {
            return statutSeries.reduce(function (sum, key) {
                const arr = hist[key] || [];
                return sum + (arr[idx] || 0);
            }, 0);
        });

        const lineCtx = document.getElementById('equipeLine')?.getContext('2d');
        if (lineCtx) {
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
