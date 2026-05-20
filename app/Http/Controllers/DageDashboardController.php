<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Service;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DageDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $statutFilter = $request->get('statut');
        $moisFilter = $request->get('mois');
        $anneeFilter = $request->get('annee', Carbon::now()->year);
        $semaineFilter = $request->get('semaine');
        $serviceFilter = $request->get('service');
        $serviceDemandeurFilter = $request->get('service_demandeur');
        $siteFilter = $request->get('site');
        $natureFilter = $request->get('nature');
        $teamTypeFilter = $request->get('team_type');
        $search = $request->get('search');
        $uniteFilter = $request->get('unite');
        $perPage = $request->get('per_page', 25);

        $query = Demande::with([
            'user', 'service', 'site', 'approbateurN1', 'approvedBy', 'rejectedBy', 'rejectedByN2',
            'sad', 'seg', 'umt', 'ubt', 'unsp', 'umr', 'utgc', 'terminatedBy', 'cloturedBy'
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'LIKE', "%{$search}%")
                  ->orWhere('objet', 'LIKE', "%{$search}%")
                  ->orWhere('observation', 'LIKE', "%{$search}%")
                  ->orWhere('nature', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', fn($s) => $s->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($statutFilter && $statutFilter !== 'tous') $query->where('statut', $statutFilter);
        if ($anneeFilter) $query->whereYear('created_at', $anneeFilter);
        if ($moisFilter) $query->whereMonth('created_at', $moisFilter);
        if ($semaineFilter) {
            $startOfWeek = Carbon::create($anneeFilter)->startOfYear()->addWeeks($semaineFilter - 1);
            $query->whereBetween('created_at', [$startOfWeek, $startOfWeek->copy()->endOfWeek()]);
        }
        if ($serviceFilter && $serviceFilter !== 'tous') {
            $natures = $this->getNaturesByService($serviceFilter);
            if (!empty($natures)) $query->whereIn('nature', $natures);
        }
        if ($natureFilter && $natureFilter !== 'tous') $query->where('nature', $natureFilter);
        if (in_array($teamTypeFilter, ['interne', 'externe'], true)) $query->where('team_type', $teamTypeFilter);
        if ($serviceDemandeurFilter && $serviceDemandeurFilter !== 'tous') $query->where('service_id', $serviceDemandeurFilter);
        if ($siteFilter && $siteFilter !== 'tous') $query->where('site_id', $siteFilter);
        if ($uniteFilter && $uniteFilter !== 'tous') $query->where('unite_code', $uniteFilter);

        // Base query (tous filtres appliqués) pour les graphiques
        $baseQuery = clone $query;

        $totalDemandes = (clone $query)->count();
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];
        $statsParStatut = [];
        foreach ($statuts as $s) $statsParStatut[$s] = (clone $query)->where('statut', $s)->count();

        $demandes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $services = $this->getServicesFromStructure();
        $unites = $this->getAllUnites();
        $servicesDemandeurs = Service::orderBy('libelle')->get();
        $sites = Site::orderBy('libelle')->get();
        $annees = $this->getAvailableYears();
        $natures = $this->getAllNatures();

        // Graphiques basés sur la requête filtrée
        $repartitionStatuts = $this->getRepartitionStatuts($baseQuery);
        $evolutionMensuelle = $this->getEvolutionMensuelle($baseQuery);

        // Travaux en cours et périodes validées (vue DAGE)
        $travauxEnCours = Demande::where('statut', 'en_cours')
            ->with(['user', 'site', 'service'])
            ->orderBy('date_debut_intervention', 'desc')
            ->limit(10)
            ->get();

        $periodesValidees = Demande::where('periode_validee_seg', true)
            ->orWhere('periode_validee_umr', true)
            ->with(['user', 'site', 'service'])
            ->orderBy('date_debut_intervention', 'desc')
            ->limit(10)
            ->get();

        return view('dage.dashboard', compact(
            'totalDemandes', 'statsParStatut', 'demandes',
            'repartitionStatuts', 'evolutionMensuelle',
            'travauxEnCours', 'periodesValidees',
            'statutFilter', 'moisFilter', 'anneeFilter', 'semaineFilter', 'serviceFilter',
            'natureFilter', 'teamTypeFilter', 'serviceDemandeurFilter', 'siteFilter', 'search', 'perPage',
            'services', 'servicesDemandeurs', 'sites', 'annees', 'natures',
            'uniteFilter', 'unites'
        ));
    }

    private function getDemandesParMois()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $data[] = [
                'mois' => $date->format('M Y'),
                'count' => Demande::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count()
            ];
        }
        return $data;
    }

    private function getDemandesParSemaine()
    {
        $data = [];
        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $data[] = [
                'semaine' => 'S' . $startOfWeek->weekOfYear,
                'count' => Demande::whereBetween('created_at', [$startOfWeek, $startOfWeek->copy()->endOfWeek()])->count()
            ];
        }
        return $data;
    }

    private function getRepartitionStatuts($baseQuery)
    {
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];
        $data = [];
        foreach ($statuts as $s) {
            $count = (clone $baseQuery)->where('statut', $s)->count();
            $data[] = ['statut' => $this->formatStatutLabel($s), 'count' => $count];
        }
        return $data;
    }

    private function getEvolutionMensuelle($baseQuery)
    {
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthData = ['mois' => $date->format('M Y')];
            foreach ($statuts as $s) {
                $monthData[$s] = (clone $baseQuery)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('statut', $s)
                    ->count();
            }
            $data[] = $monthData;
        }
        return $data;
    }

    private function getServicesFromStructure()
    {
        $structure = config('services_structure.services_structure');
        $services = [];
        foreach (['SA', 'SEG'] as $serviceType) {
            if (isset($structure[$serviceType])) {
                $services[$serviceType] = ['nom' => $structure[$serviceType]['name'], 'unites' => []];
                foreach ($structure[$serviceType]['unites'] as $uniteCode => $uniteData) {
                    $services[$serviceType]['unites'][$uniteCode] = $uniteData['name'];
                }
            }
        }
        return $services;
    }

    private function getNaturesByService($serviceCode)
    {
        $structure = config('services_structure.services_structure');
        $natures = [];
        if (isset($structure[$serviceCode]['unites'])) {
            foreach ($structure[$serviceCode]['unites'] as $unite) {
                $natures = array_merge($natures, array_keys($unite['natures']));
            }
        }
        return array_unique($natures);
    }

    private function getAvailableYears()
    {
        $currentYear = Carbon::now()->year;
        $firstYear = Demande::min('created_at');
        $startYear = $firstYear ? Carbon::parse($firstYear)->year : $currentYear;
        $years = [];
        for ($year = $currentYear; $year >= $startYear; $year--) $years[] = $year;
        return $years;
    }

    private function getAllNatures()
    {
        $structure = config('services_structure.services_structure');
        $natures = [];
        foreach (['SA', 'SEG'] as $serviceType) {
            if (isset($structure[$serviceType]['unites'])) {
                foreach ($structure[$serviceType]['unites'] as $unite) {
                    $natures = array_merge($natures, array_keys($unite['natures']));
                }
            }
        }
        return array_unique($natures);
    }

    private function getAllUnites()
    {
        $structure = config('services_structure.services_structure');
        $unites = [];
        foreach (['SA', 'SEG'] as $serviceType) {
            if (isset($structure[$serviceType]['unites'])) {
                foreach ($structure[$serviceType]['unites'] as $code => $unite) {
                    $unites[$code] = $unite['name'] ?? $code;
                }
            }
        }
        return $unites;
    }

    private function formatStatutLabel($statut)
    {
        return [
            'brouillon' => 'Brouillon', 'en_attente' => 'En Attente', 'en_cours' => 'En Cours',
            'accepte' => 'Accepté', 'rejete' => 'Rejeté', 'valide' => 'Validé',
            'impute' => 'Imputé', 'termine' => 'Terminé', 'cloture' => 'Clôturé'
        ][$statut] ?? ucfirst($statut);
    }

    public function exportExcel(Request $request)
    {
        // On réutilise la même logique de filtres que pour le dashboard
        $statutFilter = $request->get('statut');
        $moisFilter = $request->get('mois');
        $anneeFilter = $request->get('annee', Carbon::now()->year);
        $semaineFilter = $request->get('semaine');
        $serviceFilter = $request->get('service');
        $serviceDemandeurFilter = $request->get('service_demandeur');
        $siteFilter = $request->get('site');
        $natureFilter = $request->get('nature');
        $teamTypeFilter = $request->get('team_type');
        $search = $request->get('search');
        $uniteFilter = $request->get('unite');

        $query = Demande::with(['user', 'service', 'site', 'sad', 'seg', 'umt', 'ubt', 'unsp', 'umr', 'utgc']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'LIKE', "%{$search}%")
                  ->orWhere('objet', 'LIKE', "%{$search}%")
                  ->orWhere('observation', 'LIKE', "%{$search}%")
                  ->orWhere('nature', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', fn($s) => $s->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($statutFilter && $statutFilter !== 'tous') $query->where('statut', $statutFilter);
        if ($anneeFilter) $query->whereYear('created_at', $anneeFilter);
        if ($moisFilter) $query->whereMonth('created_at', $moisFilter);
        if ($semaineFilter) {
            $startOfWeek = Carbon::create($anneeFilter)->startOfYear()->addWeeks($semaineFilter - 1);
            $query->whereBetween('created_at', [$startOfWeek, $startOfWeek->copy()->endOfWeek()]);
        }
        if ($serviceFilter && $serviceFilter !== 'tous') {
            $natures = $this->getNaturesByService($serviceFilter);
            if (!empty($natures)) $query->whereIn('nature', $natures);
        }
        if ($natureFilter && $natureFilter !== 'tous') $query->where('nature', $natureFilter);
        if (in_array($teamTypeFilter, ['interne', 'externe'], true)) $query->where('team_type', $teamTypeFilter);
        if ($serviceDemandeurFilter && $serviceDemandeurFilter !== 'tous') $query->where('service_id', $serviceDemandeurFilter);
        if ($siteFilter && $siteFilter !== 'tous') $query->where('site_id', $siteFilter);
        if ($uniteFilter && $uniteFilter !== 'tous') $query->where('unite_code', $uniteFilter);

        $demandes = $query->orderBy('created_at', 'desc')->get();

        $filename = 'demandes_dage_' . Carbon::now()->format('Y-m-d_H-i-s');
        if ($statutFilter && $statutFilter !== 'tous') $filename .= '_' . $statutFilter;
        if ($anneeFilter) $filename .= '_' . $anneeFilter;
        if ($moisFilter) $filename .= '_mois-' . $moisFilter;
        $filename .= '.csv';

        return response()->streamDownload(function () use ($demandes) {
            $handle = fopen('php://output', 'w');
            // BOM pour bonne ouverture Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'N° Demande', 'Statut', 'Date création',
                'Demandeur', 'Objet', 'Nature',
                'Site', 'Service demandeur',
                'SAD', 'SEG', 'UMT', 'UBT', 'UNSP', 'UMR', 'UTGC',
            ], ';');

            foreach ($demandes as $demande) {
                fputcsv($handle, [
                    $demande->numero_demande,
                    $demande->statut,
                    optional($demande->created_at)->format('d/m/Y H:i'),
                    optional($demande->user)->name,
                    $demande->objet,
                    $demande->nature,
                    optional($demande->site)->libelle,
                    optional($demande->service)->libelle,
                    optional($demande->sad)->name,
                    optional($demande->seg)->name,
                    optional($demande->umt)->name,
                    optional($demande->ubt)->name,
                    optional($demande->unsp)->name,
                    optional($demande->umr)->name,
                    optional($demande->utgc)->name,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
