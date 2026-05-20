<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Demande;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Helpers\ServiceRedirectionHelper;
use Carbon\Carbon;

class SegController extends Controller
{
    public function index()
    {
        $segs = User::role('seg')->get();
        return view('seg.index', compact('segs'));
    }

    public function edit(string $id)
    {
        $demande = Demande::with(['service', 'site'])->find($id);
        $umrs = User::role('umr')->get();
        $utgcs = User::role('utgc')->get();
        return view('seg.editer', compact('demande', 'umrs', 'utgcs'));
    }

    public function update(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);
        $demande->umr_id = $request->input('umr_id');
        $demande->utgc_id = $request->input('utgc_id');
        $demande->save();

        $demande = $demande->fresh(['umr', 'utgc']);
        if ($demande->umr_id && $demande->umr) {
            NotificationService::demandeAssigneeUnite($demande, $demande->umr, 'UMR');
        }
        if ($demande->utgc_id && $demande->utgc) {
            NotificationService::demandeAssigneeUnite($demande, $demande->utgc, 'UTGC');
        }

        return redirect()->route('seg.demandes.approuvees')->with('success', 'Demande mise à jour avec succès');
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];

        // Natures SEG (pour retrouver les demandes à approuver)
        $segNatures = [];
        $structure = config('services_structure.services_structure');
        foreach ($structure['SEG']['unites'] as $unite) {
            $segNatures = array_merge($segNatures, array_keys($unite['natures']));
        }

        // Base des demandes déjà prises en charge par SEG
        $baseSegQuery = fn () => Demande::where('seg_id', $user->id);

        // Base des demandes "à approuver" côté SEG (statut = accepte, correspondant aux unités SEG)
        $baseSegApprouveesQuery = fn () => Demande::where('statut', 'accepte')
            ->where(function ($query) use ($segNatures) {
                $query->where(function ($q) use ($segNatures) {
                    $q->whereIn('nature', $segNatures)->where('nature', '!=', 'Autres demandes');
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereIn('unite_code', ['UTGC', 'UMR']);
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereNotNull('seg_id')->whereNull('sad_id');
                });
            });

        // Compteurs globaux
        $demandesBrouillon = $baseSegQuery()->where('statut', 'brouillon')->count();
        $demandesEnAttente = $baseSegQuery()->where('statut', 'en_attente')->count();
        $demandesEnCours   = $baseSegQuery()->where('statut', 'en_cours')->count();
        // "acceptees" = demandes à approuver SEG
        $demandesAcceptees = $baseSegApprouveesQuery()->count();
        $demandesRejetees  = $baseSegQuery()->where('statut', 'rejete')->count();
        $demandesValides   = $baseSegQuery()->where('statut', 'valide')->count();
        $demandesImputees  = $baseSegQuery()->where('statut', 'impute')->count();
        $demandesTerminees = $baseSegQuery()->where('statut', 'termine')->count();
        $demandesCloturees = $baseSegQuery()->where('statut', 'cloture')->count();

        $totalDemandes = $demandesBrouillon + $demandesEnAttente + $demandesEnCours + $demandesAcceptees
            + $demandesRejetees + $demandesValides + $demandesImputees + $demandesTerminees + $demandesCloturees;

        // Répartition par statut sur le mois sélectionné
        $demandesParMois = [];
        $pourcentagesParStatut = [];
        foreach ($statuts as $statut) {
            if ($statut === 'accepte') {
                $count = $baseSegApprouveesQuery()
                    ->whereMonth('created_at', $mois)
                    ->whereYear('created_at', $annee)
                    ->count();
            } else {
                $count = $baseSegQuery()
                    ->where('statut', $statut)
                    ->whereMonth('created_at', $mois)
                    ->whereYear('created_at', $annee)
                    ->count();
            }
            $demandesParMois[$statut] = $count;
        }
        $totalFiltre = array_sum($demandesParMois);
        foreach ($statuts as $statut) {
            $pourcentagesParStatut[$statut] = $totalFiltre > 0 ? round(($demandesParMois[$statut] / $totalFiltre) * 100, 1) : 0;
        }

        // Évolution 12 mois
        $derniersDouzeData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $moisData = [];
            foreach ($statuts as $statut) {
                if ($statut === 'accepte') {
                    $moisData[$statut] = $baseSegApprouveesQuery()
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                } else {
                    $moisData[$statut] = $baseSegQuery()
                        ->where('statut', $statut)
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                }
            }
            $derniersDouzeData[] = ['mois' => $date->format('M Y'), 'data' => $moisData];
        }

        $periodesEnAttente = Demande::where('seg_id', $user->id)
            ->whereNotNull('date_debut_intervention')->whereNotNull('date_fin_intervention')
            ->where('periode_validee_seg', false)->whereNotIn('statut', ['cloture', 'termine'])
            ->with(['user', 'site', 'service', 'umr', 'utgc'])
            ->orderBy('date_debut_intervention', 'asc')->get();

        $travauxDebutes = Demande::where(function ($query) {
            $query->whereNotNull('umr_id')->orWhereNotNull('utgc_id');
        })->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'chefequipe', 'umr', 'utgc'])
            ->orderBy('date_debut_intervention', 'desc')->limit(10)->get();

        // Périodes à venir : uniquement les interventions à partir d'aujourd'hui (ou plus tard)
        $periodesAvenir = Demande::where(function ($query) {
                $query->whereNotNull('umr_id')->orWhereNotNull('utgc_id');
            })
            ->where('seg_id', $user->id)
            ->whereNotNull('date_debut_intervention')
            ->whereNotNull('date_fin_intervention')
            ->where('periode_validee_seg', true)
            ->whereDate('date_fin_intervention', '>=', now()->toDateString())
            ->whereNotIn('statut', ['cloture', 'termine'])
            ->with(['user', 'site', 'service', 'umr', 'utgc'])
            ->orderBy('date_debut_intervention', 'asc')
            ->limit(10)
            ->get();

        $dashboardQuery = $this->buildDashboardQuery($user);
        $this->applyDashboardFilters($dashboardQuery, $request);

        // Rendre compteurs + graphiques dynamiques selon les filtres
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

        $unites = collect(config('services_structure.services_structure.SEG.unites', []))
            ->mapWithKeys(fn ($item, $code) => [$code => ($item['name'] ?? $code)])
            ->toArray();

        $natures = (clone $dashboardQuery)
            ->whereNotNull('nature')
            ->select('nature')
            ->distinct()
            ->orderBy('nature')
            ->pluck('nature');

        return view('seg.dashboard', compact(
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
            'periodesEnAttente',
            'travauxDebutes',
            'periodesAvenir',
            'mois',
            'annee',
            'demandesFiltrees',
            'unites',
            'natures'
        ));
    }

    public function exportDashboard(Request $request)
    {
        $query = $this->buildDashboardQuery(auth()->user());
        $this->applyDashboardFilters($query, $request);

        $demandes = $query->with(['user', 'service', 'site'])->orderBy('created_at', 'desc')->get();
        $filename = 'dashboard_seg_' . now()->format('Y-m-d_H-i-s') . '.csv';

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
        $segNatures = [];
        $structure = config('services_structure.services_structure');
        foreach ($structure['SEG']['unites'] as $unite) {
            $segNatures = array_merge($segNatures, array_keys($unite['natures']));
        }

        return Demande::where(function ($q) use ($user, $segNatures) {
            $q->where('seg_id', $user->id)
                ->orWhere(function ($inner) use ($segNatures) {
                    $inner->where('statut', 'accepte')
                        ->where(function ($sub) use ($segNatures) {
                            $sub->where(function ($x) use ($segNatures) {
                                $x->whereIn('nature', $segNatures)->where('nature', '!=', 'Autres demandes');
                            })->orWhere(function ($x) {
                                $x->where('nature', 'Autres demandes')->whereIn('unite_code', ['UTGC', 'UMR']);
                            })->orWhere(function ($x) {
                                $x->where('nature', 'Autres demandes')->whereNotNull('seg_id')->whereNull('sad_id');
                            });
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

    public function demandes_approuvees()
    {
        $segNatures = [];
        $structure = config('services_structure.services_structure');
        foreach ($structure['SEG']['unites'] as $unite) {
            $segNatures = array_merge($segNatures, array_keys($unite['natures']));
        }

        $demandes = Demande::where('statut', 'accepte')
            ->where(function ($query) use ($segNatures) {
                $query->where(function ($q) use ($segNatures) {
                    $q->whereIn('nature', $segNatures)->where('nature', '!=', 'Autres demandes');
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereIn('unite_code', ['UTGC', 'UMR']);
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereNotNull('seg_id')->whereNull('sad_id');
                });
            })
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('seg.demandes_approuvees', compact('demandes'));
    }

    public function demandes_imputees()
    {
        $user = auth()->user();
        $demandes = Demande::where('seg_id', $user->id)->where('statut', 'impute')
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy'])
            ->orderBy('updated_at', 'desc')->get();

        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
            $uniteInfo = ServiceRedirectionHelper::getUniteFromNature($demande->nature, $demande->unite_code);
            $demande->unite_destination = $uniteInfo ? $uniteInfo['code'] : null;
            $demande->unite_destination_nom = $uniteInfo ? $uniteInfo['name'] : null;
        }

        return view('seg.demandes_imputees', compact('demandes'));
    }

    public function demandes_rejetees()
    {
        $user = auth()->user();
        $demandes = Demande::where('seg_id', $user->id)->where('statut', 'rejete')
            ->whereNotNull('motif2')->with('user')
            ->orderBy('updated_at', 'desc')->get();
        return view('seg.demandes_rejetees', compact('demandes'));
    }

    public function demandes(Request $request)
    {
        $user = auth()->user();
        $statut = $request->get('statut'); // null => toutes
        $teamType = $request->get('team_type');

        // Même logique que sur le dashboard pour distinguer :
        // - demandes déjà suivies par SEG (seg_id)
        // - demandes "acceptees" à approuver par SEG (natures SEG)
        $segNatures = [];
        $structure = config('services_structure.services_structure');
        foreach ($structure['SEG']['unites'] as $unite) {
            $segNatures = array_merge($segNatures, array_keys($unite['natures']));
        }

        $baseSegQuery = fn () => Demande::where('seg_id', $user->id);

        $baseSegApprouveesQuery = function () use ($segNatures) {
            return Demande::where('statut', 'accepte')
                ->where(function ($query) use ($segNatures) {
                    $query->where(function ($q) use ($segNatures) {
                        $q->whereIn('nature', $segNatures)->where('nature', '!=', 'Autres demandes');
                    })->orWhere(function ($q) {
                        $q->where('nature', 'Autres demandes')->whereIn('unite_code', ['UTGC', 'UMR']);
                    })->orWhere(function ($q) {
                        $q->where('nature', 'Autres demandes')->whereNotNull('seg_id')->whereNull('sad_id');
                    });
                });
        };

        if ($statut === 'accepte') {
            // Vue "Approuvées" : mêmes règles que seg.demandes_approuvees
            $query = $baseSegApprouveesQuery();
        } elseif ($statut) {
            // Vue filtrée sur un statut précis suivi par SEG
            $query = $baseSegQuery()->where('statut', $statut);
        } else {
            // Vue "Total" : toutes les demandes SEG (suivies) + celles à approuver (acceptees)
            $query = Demande::query()->where(function ($q) use ($user, $segNatures) {
                $q->where('seg_id', $user->id)
                  ->orWhere(function ($inner) use ($segNatures) {
                      $inner->where('statut', 'accepte')
                            ->where(function ($sub) use ($segNatures) {
                                $sub->where(function ($qq) use ($segNatures) {
                                    $qq->whereIn('nature', $segNatures)->where('nature', '!=', 'Autres demandes');
                                })->orWhere(function ($qq) {
                                    $qq->where('nature', 'Autres demandes')->whereIn('unite_code', ['UTGC', 'UMR']);
                                })->orWhere(function ($qq) {
                                    $qq->where('nature', 'Autres demandes')->whereNotNull('seg_id')->whereNull('sad_id');
                                });
                            });
                  });
            });
        }

        if (in_array($teamType, ['interne', 'externe'], true)) {
            $query->where('team_type', $teamType);
        }

        $demandes = $query
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($demandes as $demande) {
            $demande->statut_label = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        $statutsDisponibles = [
            'accepte'  => 'Approuvées',
            'impute'   => 'Imputées',
            'valide'   => 'Validées',
            'en_cours' => 'En cours',
            'termine'  => 'Terminées',
            'cloture'  => 'Clôturées',
            'rejete'   => 'Rejetées',
        ];

        return view('seg.demande', compact('demandes', 'statut', 'statutsDisponibles', 'teamType'));
    }

    public function periodesEnAttente()
    {
        $user = auth()->user();
        $demandes = Demande::where('seg_id', $user->id)
            // uniquement les demandes SEG pour UMR
            ->where(function ($query) {
                $query->where('unite_code', 'UMR')
                      // sécurité pour les anciennes données où unite_code est null mais umr_id est déjà renseigné
                      ->orWhere(function ($q) {
                          $q->whereNull('unite_code')->whereNotNull('umr_id');
                      });
            })
            // soit déjà imputée, soit encore au statut "accepte" mais avec période proposée
            ->whereIn('statut', ['accepte', 'impute'])
            ->whereNotNull('date_debut_intervention')
            ->whereNotNull('date_fin_intervention')
            ->where(function ($query) {
                $query->where('periode_validee_seg', false)
                      ->orWhereNull('periode_validee_seg');
            })
            ->with(['user', 'service', 'site'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('seg.periodes_attente', compact('demandes'));
    }

    public function validerPeriode(Request $request, Demande $demande)
    {
        // On accepte les demandes UMR identifiées soit par unite_code='UMR', soit (anciens enregistrements) par umr_id non nul
        $isUmr = ($demande->unite_code === 'UMR') || !is_null($demande->umr_id);
        if (!$isUmr || !$demande->date_debut_intervention || !$demande->date_fin_intervention) {
            return redirect()->back()->withErrors('Cette demande n\'a pas de période d\'intervention proposée.');
        }

        $action = $request->input('action');

        if ($action === 'valider') {
            $demande->update([
                'periode_validee_seg' => true,
                'commentaire_periode_seg' => $request->input('commentaire'),
            ]);
            NotificationService::periodeValideeSeg($demande);
            return redirect()->back()->with('success', 'Période d\'intervention validée avec succès.');
        } elseif ($action === 'rejeter') {
            $request->validate(['commentaire' => 'required|string']);
            $demande->update([
                'periode_validee_seg' => false,
                'commentaire_periode_seg' => $request->input('commentaire'),
                'date_debut_intervention' => null,
                'date_fin_intervention' => null,
            ]);
            NotificationService::periodeRejetee($demande, $request->input('commentaire'));
            return redirect()->back()->with('success', 'Période d\'intervention rejetée.');
        }

        return redirect()->back()->withErrors('Action non reconnue.');
    }

    public function modifierPeriode(Request $request, Demande $demande)
    {
        $isUmr = ($demande->unite_code === 'UMR') || !is_null($demande->umr_id);
        if (!$isUmr || $demande->statut !== 'impute') {
            return redirect()->back()->withErrors('Cette action n\'est disponible que pour les demandes UMR imputées.');
        }

        $request->validate([
            'date_debut_intervention' => 'required|date',
            'date_fin_intervention' => 'required|date|after_or_equal:date_debut_intervention',
        ]);

        $demande->update([
            'date_debut_intervention' => $request->input('date_debut_intervention'),
            'date_fin_intervention' => $request->input('date_fin_intervention'),
            'commentaire_periode_seg' => $request->input('commentaire'),
            'periode_validee_seg' => true,
        ]);

        NotificationService::periodeModifieeSeg($demande->fresh());

        return redirect()->back()->with('success', 'Période d\'intervention modifiée avec succès.');
    }

    public function rejeterPeriodeImputee(Request $request, Demande $demande)
    {
        $isUmr = ($demande->unite_code === 'UMR') || !is_null($demande->umr_id);
        if (!$isUmr || $demande->statut !== 'impute') {
            return redirect()->back()->withErrors('Cette action n\'est disponible que pour les demandes UMR imputées.');
        }

        $request->validate(['motif' => 'required|string|min:10']);

        $demande->update([
            'statut' => 'accepte',
            'date_debut_intervention' => null,
            'date_fin_intervention' => null,
            'periode_validee_seg' => false,
            'commentaire_periode_seg' => $request->input('motif'),
            'umr_id' => null,
        ]);

        NotificationService::periodeRejetee($demande, $request->input('motif'));
        return redirect()->back()->with('success', 'Période rejetée. La demande a été renvoyée au demandeur.');
    }
}
