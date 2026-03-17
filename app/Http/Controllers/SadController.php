<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Demande;
use Illuminate\Http\Request;
use App\Helpers\ServiceRedirectionHelper;
use Carbon\Carbon;

class SadController extends Controller
{
    public function index()
    {
        $sads = User::role('sad')->get();
        return view('sad.index', compact('sads'));
    }

    public function edit(string $id)
    {
        $demande = Demande::with(['service', 'site'])->find($id);
        $umts = User::role('umt')->get();
        $ubts = User::role('ubt')->get();
        $unsps = User::role('unsp')->get();

        return view('sad.editer', compact('demande', 'umts', 'ubts', 'unsps'));
    }

    public function update(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);
        $demande->umt_id = $request->input('umt_id');
        $demande->ubt_id = $request->input('ubt_id');
        $demande->unsp_id = $request->input('unsp_id');
        $demande->save();

        return redirect()->route('sad.demandes.approuvees')->with('success', 'Demande mise à jour avec succès');
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];

        // Demandes imputées / suivies directement par ce SAD
        $baseSadQuery = fn () => Demande::where('sad_id', $user->id);

        // Demandes approuvées relevant du SA (toutes les demandes SA en attente de dispatch),
        // même si elles ne sont pas encore affectées à un SAD en particulier.
        $saNatures = [];
        $structure = config('services_structure.services_structure');
        foreach ($structure['SA']['unites'] as $unite) {
            $saNatures = array_merge($saNatures, array_keys($unite['natures']));
        }

        $baseSaApprouveesQuery = fn () => Demande::where('statut', 'accepte')
            ->where(function ($query) use ($saNatures) {
                $query->where(function ($q) use ($saNatures) {
                    $q->whereIn('nature', $saNatures)->where('nature', '!=', 'Autres demandes');
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereIn('unite_code', ['UAG', 'UPNS', 'UGBT']);
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereNotNull('sad_id')->whereNull('seg_id');
                });
            });

        // Compteurs globaux
        $demandesBrouillon = $baseSadQuery()->where('statut', 'brouillon')->count();
        $demandesEnAttente = $baseSadQuery()->where('statut', 'en_attente')->count();
        $demandesEnCours = $baseSadQuery()->where('statut', 'en_cours')->count();
        $demandesAcceptees = $baseSaApprouveesQuery()->count();
        $demandesRejetees = $baseSadQuery()->where('statut', 'rejete')->count();
        $demandesValides = $baseSadQuery()->where('statut', 'valide')->count();
        $demandesImputees = $baseSadQuery()->where('statut', 'impute')->count();
        $demandesTerminees = $baseSadQuery()->where('statut', 'termine')->count();
        $demandesCloturees = $baseSadQuery()->where('statut', 'cloture')->count();

        $totalDemandes = $demandesBrouillon + $demandesEnAttente + $demandesEnCours + $demandesAcceptees
            + $demandesRejetees + $demandesValides + $demandesImputees + $demandesTerminees + $demandesCloturees;

        $demandesParMois = [];
        $pourcentagesParStatut = [];
        foreach ($statuts as $statut) {
            if ($statut === 'accepte') {
                $count = $baseSaApprouveesQuery()
                    ->whereMonth('created_at', $mois)
                    ->whereYear('created_at', $annee)
                    ->count();
            } else {
                $count = $baseSadQuery()
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

        $derniersDouzeData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $moisData = [];
            foreach ($statuts as $statut) {
                if ($statut === 'accepte') {
                    $moisData[$statut] = $baseSaApprouveesQuery()
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                } else {
                    $moisData[$statut] = $baseSadQuery()
                        ->where('statut', $statut)
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                }
            }
            $derniersDouzeData[] = ['mois' => $date->format('M Y'), 'data' => $moisData];
        }

        $travauxDebutes = Demande::where(function ($query) {
            $query->whereNotNull('umt_id')->orWhereNotNull('ubt_id')->orWhereNotNull('unsp_id');
        })->where('sad_id', $user->id)->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'chefequipe', 'umt', 'ubt', 'unsp'])
            ->orderBy('date_debut_intervention', 'desc')->limit(10)->get();

        $periodesAvenir = Demande::where(function ($query) {
            $query->whereNotNull('umt_id')->orWhereNotNull('ubt_id')->orWhereNotNull('unsp_id');
        })->where('sad_id', $user->id)->whereNotNull('date_debut_intervention')
            ->whereNotNull('date_fin_intervention')
            ->whereBetween('updated_at', [now()->subDays(30), now()])
            ->whereNotIn('statut', ['cloture', 'termine'])
            ->with(['user', 'site', 'service', 'umt', 'ubt', 'unsp'])
            ->orderBy('updated_at', 'desc')->limit(10)->get();

        return view('sad.dashboard', compact(
            'totalDemandes', 'demandesBrouillon', 'demandesEnAttente', 'demandesEnCours',
            'demandesAcceptees', 'demandesRejetees', 'demandesValides', 'demandesImputees',
            'demandesTerminees', 'demandesCloturees', 'demandesParMois', 'pourcentagesParStatut',
            'derniersDouzeData', 'travauxDebutes', 'periodesAvenir', 'mois', 'annee'
        ));
    }

    public function demandes_approuvees()
    {
        $saNatures = [];
        $structure = config('services_structure.services_structure');
        foreach ($structure['SA']['unites'] as $unite) {
            $saNatures = array_merge($saNatures, array_keys($unite['natures']));
        }

        $demandes = Demande::where('statut', 'accepte')
            ->where(function ($query) use ($saNatures) {
                $query->where(function ($q) use ($saNatures) {
                    $q->whereIn('nature', $saNatures)->where('nature', '!=', 'Autres demandes');
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereIn('unite_code', ['UAG', 'UPNS', 'UGBT']);
                })->orWhere(function ($q) {
                    $q->where('nature', 'Autres demandes')->whereNotNull('sad_id')->whereNull('seg_id');
                });
            })
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        return view('sad.demandes_approuvees', compact('demandes'));
    }

    public function demandes_imputees()
    {
        $user = auth()->user();
        $demandes = Demande::where('sad_id', $user->id)->where('statut', 'impute')
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy'])
            ->orderBy('updated_at', 'desc')->get();

        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
            $uniteInfo = ServiceRedirectionHelper::getUniteFromNature($demande->nature, $demande->unite_code);
            $demande->unite_destination = $uniteInfo ? $uniteInfo['code'] : null;
            $demande->unite_destination_nom = $uniteInfo ? $uniteInfo['name'] : null;
        }

        return view('sad.demandes_imputees', compact('demandes'));
    }

    public function demandes_rejetees()
    {
        $user = auth()->user();
        $demandes = Demande::where('sad_id', $user->id)->where('statut', 'rejete')
            ->whereNotNull('motif2')->with('user')
            ->orderBy('updated_at', 'desc')->get();

        return view('sad.demandes_rejetees', compact('demandes'));
    }

    public function demandes(Request $request)
    {
        $user = auth()->user();

        $statut = $request->get('statut', 'accepte');

        $query = Demande::query();

        if ($statut === 'accepte') {
            // Reprendre la même logique que demandes_approuvees pour les demandes SA
            $saNatures = [];
            $structure = config('services_structure.services_structure');
            foreach ($structure['SA']['unites'] as $unite) {
                $saNatures = array_merge($saNatures, array_keys($unite['natures']));
            }

            $query->where('statut', 'accepte')
                ->where(function ($q) use ($saNatures) {
                    $q->where(function ($inner) use ($saNatures) {
                        $inner->whereIn('nature', $saNatures)->where('nature', '!=', 'Autres demandes');
                    })->orWhere(function ($inner) {
                        $inner->where('nature', 'Autres demandes')->whereIn('unite_code', ['UAG', 'UPNS', 'UGBT']);
                    })->orWhere(function ($inner) {
                        $inner->where('nature', 'Autres demandes')->whereNotNull('sad_id')->whereNull('seg_id');
                    });
                });
        } else {
            $query->where('sad_id', $user->id)
                ->where('statut', $statut);
        }

        $demandes = $query
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($demandes as $demande) {
            $demande->statut_label = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        // Filtres disponibles pour la liste SAD (sans Brouillon / En attente)
        $statutsDisponibles = [
            'accepte'  => 'Approuvées',
            'impute'   => 'Imputées',
            'valide'   => 'Validées',
            'en_cours' => 'En cours',
            'termine'  => 'Terminées',
            'cloture'  => 'Clôturées',
            'rejete'   => 'Rejetées',
        ];

        return view('sad.demande', compact('demandes', 'statut', 'statutsDisponibles'));
    }
}
