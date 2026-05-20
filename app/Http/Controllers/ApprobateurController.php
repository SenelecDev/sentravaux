<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\DemandeRejection;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprobateurController extends Controller
{
    public function index()
    {
        $approbateurs = User::role('approbateur')->get();
        return view('approbateur.index', compact('approbateurs'));
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];

        $totalDemandes = Demande::where('approbateur_n1_id', $user->id)->count();
        $demandesBrouillon = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'brouillon')->count();
        $demandesEnAttente = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'en_attente')->count();
        $demandesEnCours = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'en_cours')->count();
        $demandesAcceptees = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'accepte')->count();
        $demandesRejetees = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'rejete')->count();
        $demandesValides = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'valide')->count();
        $demandesImputees = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'impute')->count();
        $demandesTerminees = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'termine')->count();
        $demandesCloturees = Demande::where('approbateur_n1_id', $user->id)->where('statut', 'cloture')->count();

        $demandesParMois = [];
        $pourcentagesParStatut = [];
        foreach ($statuts as $statut) {
            $count = Demande::where('approbateur_n1_id', $user->id)->where('statut', $statut)
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
                $moisData[$statut] = Demande::where('approbateur_n1_id', $user->id)->where('statut', $statut)
                    ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
            }
            $derniersDouzeData[] = ['mois' => $date->format('M Y'), 'data' => $moisData];
        }

        return view('approbateur.dashboard', compact(
            'totalDemandes', 'demandesBrouillon', 'demandesEnAttente', 'demandesEnCours',
            'demandesAcceptees', 'demandesRejetees', 'demandesValides', 'demandesImputees',
            'demandesTerminees', 'demandesCloturees', 'demandesParMois', 'pourcentagesParStatut',
            'derniersDouzeData', 'mois', 'annee'
        ));
    }

    public function demandes()
    {
        $user = Auth::user();
        $demandes = Demande::where('approbateur_n1_id', $user->id)
            ->where('statut', 'en_attente')
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        return view('approbateur.demande', compact('demandes'));
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $demande = Demande::findOrFail($id);

            $status = $request->input('status');

            if ($status === 'approuve') {
                $demande->statut = 'accepte';
                $demande->approved_by = auth()->user()->id;
                $demande->commentaire_approbation = $request->commentaire;

                // Affecter automatiquement au service SA / SEG / SGB selon nature + unite_code
                $uniteInfo = \App\Helpers\ServiceRedirectionHelper::getUniteFromNature($demande->nature, $demande->unite_code);

                if ($uniteInfo) {
                    $serviceRole = match ($uniteInfo['service']) {
                        'SA' => 'sad',
                        'SEG' => 'seg',
                        'SGB' => 'sgb',
                        default => null,
                    };

                    if (!$serviceRole) {
                        throw new \RuntimeException('Service de redirection non géré: ' . ($uniteInfo['service'] ?? 'inconnu'));
                    }

                    $serviceManager = User::whereHas('roles', function ($query) use ($serviceRole) {
                        $query->where('name', $serviceRole);
                    })->first();

                    if ($serviceManager) {
                        if ($uniteInfo['service'] === 'SA') {
                            $demande->sad_id = $serviceManager->id;
                        } elseif ($uniteInfo['service'] === 'SEG') {
                            $demande->seg_id = $serviceManager->id;
                        } elseif ($uniteInfo['service'] === 'SGB') {
                            $demande->sgb_id = $serviceManager->id;
                        }
                    }
                }

                NotificationService::demandeApprouvee($demande, auth()->user());
            }

            if ($status === 'rejete') {
                $demande->statut = 'rejete';
                $demande->motif = $request->motif;
                $demande->rejected_by = auth()->user()->id;
                DemandeRejection::create([
                    'demande_id' => $demande->id,
                    'rejected_by' => auth()->id(),
                    'rejection_level' => 'n1',
                    'reason' => (string) $request->motif,
                    'rejected_at' => now(),
                ]);
                NotificationService::demandeRejetee($demande, auth()->user(), $request->motif);
            }

            $demande->save();

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('demande.show', $demande)
                ->with('success', 'Le statut de la demande a été mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Error in updateStatus: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return redirect()
                ->back()
                ->withErrors("Une erreur est survenue lors de la mise à jour du statut de la demande.");
        }
    }

    public function demandesApprouvees(Request $request)
    {
        $userId = auth()->user()->id;
        $statut = $request->get('statut'); // accepte, impute, termine, cloture, ...

        $query = Demande::where('approved_by', $userId);

        if ($statut) {
            $query->where('statut', $statut);
        }

        $demandes = $query
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy'])
            ->get();

        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        $statutsDisponibles = [
            'accepte'   => 'Approuvées',
            'impute'    => 'Imputées / dispatchées',
            'valide'    => 'Validées',
            'en_cours'  => 'En cours',
            'brouillon' => 'Brouillon',
            'termine'   => 'Terminées',
            'cloture'   => 'Clôturées',
        ];

        return view('approbateur.approuve', compact('demandes', 'statut', 'statutsDisponibles'));
    }

    public function demandesRejetees()
    {
        $userId = auth()->user()->id;
        $demandes = Demande::where('statut', 'rejete')
            ->where('rejected_by', $userId)
            ->with(['user', 'service', 'site', 'approbateurN1', 'images', 'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy'])
            ->get();

        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        return view('approbateur.rejete', compact('demandes'));
    }
}
