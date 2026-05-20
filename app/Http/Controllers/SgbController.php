<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SgbController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];

        $dashboardQuery = $this->buildDashboardQuery($user);
        $this->applyDashboardFilters($dashboardQuery, $request);

        $filteredBase = clone $dashboardQuery;
        $demandesBrouillon = (clone $filteredBase)->where('statut', 'brouillon')->count();
        $demandesEnAttente = (clone $filteredBase)->where('statut', 'en_attente')->count();
        $demandesEnCours = (clone $filteredBase)->where('statut', 'en_cours')->count();
        $demandesAcceptees = (clone $filteredBase)->where('statut', 'accepte')->count();
        $demandesRejetees = (clone $filteredBase)->where('statut', 'rejete')->count();
        $demandesValides = (clone $filteredBase)->where('statut', 'valide')->count();
        $demandesImputees = (clone $filteredBase)->where('statut', 'impute')->count();
        $demandesTerminees = (clone $filteredBase)->where('statut', 'termine')->count();
        $demandesCloturees = (clone $filteredBase)->where('statut', 'cloture')->count();
        $totalDemandes = (clone $filteredBase)->count();

        $demandesParMois = [];
        foreach ($statuts as $statut) {
            $demandesParMois[$statut] = (clone $filteredBase)
                ->where('statut', $statut)
                ->whereMonth('created_at', $mois)
                ->whereYear('created_at', $annee)
                ->count();
        }
        $totalFiltre = array_sum($demandesParMois);
        $pourcentagesParStatut = [];
        foreach ($statuts as $statut) {
            $pourcentagesParStatut[$statut] = $totalFiltre > 0 ? round(($demandesParMois[$statut] / $totalFiltre) * 100, 1) : 0;
        }

        $derniersDouzeData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $moisData = [];
            foreach ($statuts as $statut) {
                $moisData[$statut] = (clone $filteredBase)
                    ->where('statut', $statut)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
            }
            $derniersDouzeData[] = ['mois' => $date->format('M Y'), 'data' => $moisData];
        }

        $demandesFiltrees = (clone $dashboardQuery)
            ->with(['user', 'site', 'service'])
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->get('per_page', 15))
            ->withQueryString();

        $unites = collect(config('services_structure.services_structure.SGB.unites', []))
            ->mapWithKeys(fn ($item, $code) => [$code => ($item['name'] ?? $code)])
            ->toArray();

        $natures = (clone $dashboardQuery)
            ->whereNotNull('nature')
            ->select('nature')
            ->distinct()
            ->orderBy('nature')
            ->pluck('nature');

        return view('sgb.dashboard', compact(
            'totalDemandes',
            'demandesBrouillon',
            'demandesEnAttente',
            'demandesEnCours',
            'demandesAcceptees',
            'demandesRejetees',
            'demandesValides',
            'demandesImputees',
            'demandesTerminees',
            'demandesCloturees',
            'demandesParMois',
            'pourcentagesParStatut',
            'derniersDouzeData',
            'mois',
            'annee',
            'demandesFiltrees',
            'unites',
            'natures'
        ));
    }

    public function demandes(Request $request)
    {
        $query = $this->buildDashboardQuery(auth()->user());
        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }
        $demandes = $query->with(['user', 'site', 'service'])->orderBy('updated_at', 'desc')->get();
        return view('sgb.demande', compact('demandes'));
    }

    public function exportDashboard(Request $request)
    {
        $query = $this->buildDashboardQuery(auth()->user());
        $this->applyDashboardFilters($query, $request);

        $demandes = $query->with(['user', 'service', 'site'])->orderBy('created_at', 'desc')->get();
        $filename = 'dashboard_sgb_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($demandes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['N Demande', 'Statut', 'Unite', 'Nature', 'Objet', 'Demandeur', 'Service', 'Site', 'Creation', 'Debut', 'Fin'], ';');
            foreach ($demandes as $d) {
                fputcsv($handle, [
                    $d->numero_demande,
                    $d->statut,
                    $d->unite_code,
                    $d->nature,
                    $d->objet,
                    $d->user?->name,
                    $d->service?->libelle,
                    $d->site?->libelle,
                    optional($d->created_at)->format('d/m/Y H:i'),
                    optional($d->date_debut_intervention)->format('d/m/Y H:i'),
                    optional($d->date_fin_intervention)->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildDashboardQuery($user)
    {
        return Demande::where(function ($q) use ($user) {
            $q->where('sgb_id', $user->id)
                ->orWhere(function ($x) {
                    $x->where('statut', 'accepte')
                        ->where(function ($n) {
                            $n->where('nature', 'Demande de remboursement')
                                ->orWhere('nature', 'like', '%remboursement%');
                        });
                });
        });
    }

    private function applyDashboardFilters($query, Request $request): void
    {
        $unite = $request->get('unite');
        $demande = $request->get('demande');
        $teamType = $request->get('team_type');
        $nature = $request->get('nature');
        $periode = $request->get('periode', 'tous');
        $search = trim((string) $request->get('search', ''));

        if ($unite && $unite !== 'tous') $query->where('unite_code', $unite);
        if ($demande && $demande !== 'tous') $query->where('statut', $demande);
        if (in_array($teamType, ['interne', 'externe'], true)) $query->where('team_type', $teamType);
        if ($nature && $nature !== 'tous') $query->where('nature', $nature);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'like', '%' . $search . '%')
                    ->orWhere('objet', 'like', '%' . $search . '%');
            });
        }

        if ($periode === 'semaine') $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        if ($periode === 'mois') $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        if ($periode === 'annee') $query->whereYear('created_at', now()->year);
        if ($periode === 'custom') {
            if ($request->filled('periode_min')) $query->whereDate('created_at', '>=', $request->get('periode_min'));
            if ($request->filled('periode_max')) $query->whereDate('created_at', '<=', $request->get('periode_max'));
        }

        if ($request->filled('date_debut_min')) $query->whereDate('date_debut_intervention', '>=', $request->get('date_debut_min'));
        if ($request->filled('date_debut_max')) $query->whereDate('date_debut_intervention', '<=', $request->get('date_debut_max'));
        if ($request->filled('date_fin_min')) $query->whereDate('date_fin_intervention', '>=', $request->get('date_fin_min'));
        if ($request->filled('date_fin_max')) $query->whereDate('date_fin_intervention', '<=', $request->get('date_fin_max'));
    }
}

