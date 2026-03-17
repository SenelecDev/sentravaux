<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Demande;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DemandeurController extends Controller
{
    public function index()
    {
        $demandeurs = User::role('demandeur')->get();
        return view('demandeur.index', compact('demandeurs'));
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];

        $totalDemandes = Demande::where('user_id', $user->id)->count();
        $demandesBrouillon = Demande::where('user_id', $user->id)->where('statut', 'brouillon')->count();
        $demandesEnAttente = Demande::where('user_id', $user->id)->where('statut', 'en_attente')->count();
        $demandesEnCours = Demande::where('user_id', $user->id)->where('statut', 'en_cours')->count();
        $demandesAcceptees = Demande::where('user_id', $user->id)->where('statut', 'accepte')->count();
        $demandesRejetees = Demande::where('user_id', $user->id)->where('statut', 'rejete')->count();
        $demandesValides = Demande::where('user_id', $user->id)->where('statut', 'valide')->count();
        $demandesImputees = Demande::where('user_id', $user->id)->where('statut', 'impute')->count();
        $demandesTerminees = Demande::where('user_id', $user->id)->where('statut', 'termine')->count();
        $demandesCloturees = Demande::where('user_id', $user->id)->where('statut', 'cloture')->count();

        $demandesParMois = [];
        $pourcentagesParStatut = [];
        foreach ($statuts as $statut) {
            $count = Demande::where('user_id', $user->id)->where('statut', $statut)
                ->whereMonth('created_at', $mois)->whereYear('created_at', $annee)->count();
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
                $moisData[$statut] = Demande::where('user_id', $user->id)->where('statut', $statut)
                    ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
            }
            $derniersDouzeData[] = ['mois' => $date->format('M Y'), 'data' => $moisData];
        }

        return view('demandeur.dashboard', compact(
            'totalDemandes', 'demandesBrouillon', 'demandesEnAttente', 'demandesEnCours',
            'demandesAcceptees', 'demandesRejetees', 'demandesValides', 'demandesImputees',
            'demandesTerminees', 'demandesCloturees', 'demandesParMois', 'pourcentagesParStatut',
            'derniersDouzeData', 'mois', 'annee'
        ));
    }
}
