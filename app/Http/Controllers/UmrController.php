<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\User;
use App\Models\Equipe;
use App\Models\Demande;
use App\Mail\DemandeCloture;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UmrController extends Controller
{
    public function index()
    {
        $umrs = User::role("umr")->get();
        return view("umr.index", compact("umrs"));
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}

    public function edit(string $id)
    {
        $demande = Demande::with([
            'service', 'site', 'equipes', 'chefequipe',
            'superviseur', 'executant', 'seg', 'terminatedBy'
        ])->find($id);

        $equipes = Equipe::all();
        $users = User::all();

        return view('umr.editer', compact('demande', 'equipes', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);

        if ($demande->statut === 'cloture') {
            return redirect()->route('umr.demandes.recues')->with('error', 'La demande ne peut plus être modifiée.');
        }

        $rules = [
            'chef_equipe_id' => 'nullable|exists:users,id',
            'superviseur_id' => 'nullable|exists:users,id',
            'superviseur_externe_id' => 'nullable|exists:users,id',
            'executant_id' => 'nullable|exists:users,id',
            'equipes.*' => 'nullable|exists:equipes,id',
            'duree.*' => 'nullable|numeric|min:0.5',
            'equipes_noms_new.*' => 'nullable|string',
            'duree_new.*' => 'nullable|numeric|min:0.5',
            'executant_equipe_new.*' => 'nullable|exists:users,id',
            'commentaire' => 'nullable|string',
            'prestataire_nom' => 'nullable|string',
            'numero_commande' => 'nullable|string',
            'commentaire_prestataire' => 'nullable|string',
        ];

        $action = $request->input('action');
        // Pour l'action "terminer" via le modal, on n'exige pas de renvoyer team_type :
        // on se contente de la valeur déjà stockée en base.
        if (!in_array($action, ['cloturer', 'retour', 'terminer'], true)) {
            $rules['team_type'] = 'required|in:interne,externe';
        } elseif ($request->has('team_type')) {
            $rules['team_type'] = 'in:interne,externe';
        }

        if ($action === 'terminer') {
            $rules['date_intervention'] = 'required|date';
            if ($request->filled('prestataire_nom')) $rules['numero_commande'] = 'required|string';
        }

        $request->validate($rules);

        $chefEquipeUserId = $request->filled('chef_equipe_id') ? $request->chef_equipe_id : null;
        $superviseurUserId = $request->filled('superviseur_id') ? $request->superviseur_id : ($request->filled('superviseur_externe_id') ? $request->superviseur_externe_id : null);
        $executantUserId = $request->filled('executant_id') ? $request->executant_id : null;

        $updateData = $request->except(['action']);
        // Mapper correctement le commentaire UMR (champ du formulaire => colonne comment_umr)
        if ($request->has('commentaire')) {
            $updateData['comment_umr'] = $request->input('commentaire');
            unset($updateData['commentaire']);
        }
        if ($chefEquipeUserId) $updateData['chef_equipe_id'] = $chefEquipeUserId;
        if ($superviseurUserId) $updateData['superviseur_id'] = $superviseurUserId;
        if ($executantUserId) $updateData['executant_id'] = $executantUserId;

        $demande->update($updateData);

        // Sync equipes
        if ($request->has('equipes')) {
            $equipes = [];
            $executantsEquipe = $request->input('executant_equipe', []);
            foreach ($request->input('equipes', []) as $index => $equipeId) {
                if ($equipeId) {
                    $duree = $request->input('duree.' . $index);
                    $executantId = $executantsEquipe[$index] ?? null;
                    if ($duree) $equipes[$equipeId] = ['duree' => $duree, 'executant_id' => $executantId];
                }
            }
            $demande->equipes()->sync($equipes);
        } elseif (in_array($action, [null, ''], true)) {
            $demande->equipes()->detach();
        }

        if ($request->has('equipes_noms_new') && $request->has('duree_new')) {
            foreach ($request->input('equipes_noms_new', []) as $index => $nomEquipe) {
                if (!empty($nomEquipe) && ($request->input('duree_new.' . $index, 0) > 0)) {
                    $equipe = Equipe::where('nom', $nomEquipe)->first();
                    if ($equipe) {
                        $demande->equipes()->syncWithoutDetaching([
                            $equipe->id => ['duree' => $request->input('duree_new.' . $index), 'executant_id' => $request->input('executant_equipe_new.' . $index) ?: null]
                        ]);
                    }
                }
            }
        }

        switch ($action) {
            case 'dispatcher':
                if ($chefEquipeUserId) {
                    $demande->update(['chef_equipe_id' => $chefEquipeUserId, 'statut' => 'valide', 'validated_by' => auth()->id()]);
                    $chefEquipe = User::find($chefEquipeUserId);
                    if ($chefEquipe) NotificationService::equipeAffectee($demande, $chefEquipe);
                    return redirect()->route('umr.demandes.recues')->with('success', 'Demande validée et transférée au chef d\'équipe.');
                }
                return redirect()->back()->withErrors(['chef_equipe_id' => 'Chef d\'équipe doit être sélectionné.']);

            case 'retour':
                if ($demande->chef_equipe_id) {
                    $commentaireRetour = $request->input('commentaire_retour', '');
                    $msg = 'Demande renvoyée par ' . auth()->user()->name . ' le ' . now()->format('d/m/Y à H:i');
                    if ($commentaireRetour) $msg .= "\n\nCommentaire : " . $commentaireRetour;
                    $ancien = $demande->commentaire_equipe ?? '';
                    $demande->update(['statut' => 'valide', 'commentaire_equipe' => $ancien ? $ancien . "\n\n--- RETOUR ---\n" . $msg : "--- RETOUR ---\n" . $msg]);
                    $chefEquipe = User::find($demande->chef_equipe_id);
                    if ($chefEquipe) NotificationService::demandeRetournee($demande, $chefEquipe, $commentaireRetour);
                    return redirect()->route('umr.demandes.recues')->with('success', 'Demande renvoyée au chef d\'équipe.');
                }
                return redirect()->back()->withErrors(['error' => 'Aucun chef d\'équipe assigné.']);

            case 'debut_travaux':
                $demande->update(['statut' => 'en_cours', 'date_intervention' => now(), 'date_debut_intervention' => now()]);
                return redirect()->route('umr.demandes.recues')->with('success', 'Début des travaux enregistré.');

            case 'terminer':
                $termData = ['statut' => 'termine', 'terminated_by' => auth()->id(), 'date_intervention' => $request->date_intervention];
                if ($request->filled('prestataire_nom')) $termData['prestataire_nom'] = $request->prestataire_nom;
                if ($request->filled('numero_commande')) $termData['numero_commande'] = $request->numero_commande;
                if ($request->filled('commentaire_prestataire')) $termData['commentaire_prestataire'] = $request->commentaire_prestataire;
                if (!$demande->date_fin) $termData['date_fin'] = now();
                $demande->update($termData);
                return redirect()->route('umr.demandes.recues')->with('success', 'Demande terminée avec succès.');

            case 'cloturer':
                $demande->update(['statut' => 'cloture', 'cloture_par' => auth()->id()]);
                try {
                    $n1 = User::find($demande->user_id);
                    $n2 = User::find($demande->approved_by);
                    $n3 = null; $n3Name = null;
                    foreach (['umt', 'ubt', 'unsp', 'umr', 'utgc'] as $u) {
                        if ($demande->{$u . '_id'}) {
                            $n3 = User::find($demande->{$u . '_id'});
                            $n3Name = $demande->$u?->name;
                            break;
                        }
                    }
                    $signatureN1 = $n1 ? $this->convertImageToBase64($n1->signature) : null;
                    $stampN1 = $n1 ? $this->convertImageToBase64($n1->stamp) : null;
                    $signatureN2 = $n2 ? $this->convertImageToBase64($n2->signature) : null;
                    $stampN2 = $n2 ? $this->convertImageToBase64($n2->stamp) : null;
                    $signatureN3 = $n3 ? $this->convertImageToBase64($n3->signature) : null;
                    $stampN3 = $n3 ? $this->convertImageToBase64($n3->stamp) : null;

                    $pdf = new Dompdf();
                    $pdf->loadHtml(view('pdf.demande', compact('demande', 'signatureN1', 'stampN1', 'signatureN2', 'stampN2', 'signatureN3', 'stampN3', 'n3Name'))->render());
                    $pdf->setPaper('A4', 'paysage');
                    $pdf->render();
                    Storage::put('public/demandes/' . $demande->numero_demande . '.pdf', $pdf->output());

                    $clotureName = null;
                    foreach (['unsp', 'ubt', 'umt', 'umr', 'utgc'] as $u) {
                        if ($demande->$u) {
                            $clotureName = $demande->$u->name;
                            break;
                        }
                    }

                    Mail::to($demande->user->email)->send(new DemandeCloture($demande, 'demandes/' . $demande->numero_demande . '.pdf', $clotureName));
                } catch (\Throwable $e) {
                    \Log::error('Erreur lors de la génération/envoi du PDF de clôture UMR', [
                        'demande_id' => $demande->id,
                        'message'    => $e->getMessage(),
                    ]);
                }
                return redirect()->route('umr.demandes.recues')->with('success', 'Demande clôturée avec succès.');

            default:
                return redirect()->route('umr.demandes.recues')->with('success', 'Demande mise à jour avec succès.');
        }
    }

    private function convertImageToBase64($path)
    {
        if (Storage::exists('public/' . $path)) {
            $fileContent = Storage::get('public/' . $path);
            $mimeType = Storage::mimeType('public/' . $path);
            return 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
        }
        return null;
    }

    public function demandesTerminees()
    {
        $user = auth()->user();

        $demandes = Demande::where('statut', 'termine')
            ->where(function ($q) use ($user) {
                $q->where('umr_id', $user->id)
                  ->orWhereHas('umr', fn($s) => $s->where('id', $user->id));
            })
            ->with(['user', 'site', 'service', 'equipes', 'chefequipe', 'superviseur', 'executant', 'terminatedBy', 'validatedBy', 'umr'])
            ->orderBy('updated_at', 'desc')
            ->get();
        return view('umr.demandes.terminees', compact('demandes'));
    }

    public function demandesCloturees()
    {
        $user = auth()->user();

        $demandes = Demande::where('statut', 'cloture')
            ->where(function ($q) use ($user) {
                $q->where('umr_id', $user->id)
                  ->orWhereHas('umr', fn($s) => $s->where('id', $user->id));
            })
            ->with(['user', 'site', 'service', 'equipes', 'chefequipe', 'superviseur', 'executant', 'terminatedBy', 'cloturedBy', 'validatedBy', 'umr'])
            ->orderBy('updated_at', 'desc')
            ->get();
        return view('umr.demandes.cloturees', compact('demandes'));
    }

    public function destroy(string $id) {}

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));

        $baseQuery = fn() => Demande::where(function ($q) use ($user) { $q->whereNotNull('umr_id')->orWhere('umr_id', $user->id); });

        $totalDemandes = $baseQuery()->count();
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];
        $statsParStatut = [];
        foreach ($statuts as $s) $statsParStatut[$s] = $baseQuery()->where('statut', $s)->count();

        $demandesParMois = [];
        foreach ($statuts as $s) $demandesParMois[$s] = $baseQuery()->where('statut', $s)->whereMonth('created_at', $mois)->whereYear('created_at', $annee)->count();

        $totalParMois = array_sum($demandesParMois);
        $pourcentagesParStatut = $totalParMois > 0
            ? array_map(fn($c) => ($c / $totalParMois) * 100, $demandesParMois)
            : array_fill_keys($statuts, 0);

        $donneesHistoriques = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $donneesHistoriques['mois'][] = $date->format('M Y');
            foreach ($statuts as $s) $donneesHistoriques[$s][] = $baseQuery()->where('statut', $s)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
        }

        $travauxDebutes = Demande::where('umr_id', $user->id)->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'chefequipe'])->orderBy('date_debut_intervention', 'desc')->limit(10)->get();

        $periodesAvenir = Demande::where('umr_id', $user->id)
            ->whereNotNull('date_debut_intervention')->whereNotNull('date_fin_intervention')
            ->where('date_debut_intervention', '>=', now())->where('date_debut_intervention', '<=', now()->addDays(30))
            ->whereNotIn('statut', ['cloture', 'termine', 'en_cours'])
            ->with(['user', 'site', 'service'])->orderBy('date_debut_intervention', 'asc')->limit(10)->get();

        // Compteurs individuels pour le composant <x-dashboard-stats>
        $demandesBrouillon   = $statsParStatut['brouillon']    ?? 0;
        $demandesEnAttente   = $statsParStatut['en_attente']   ?? 0;
        $demandesEnCours     = $statsParStatut['en_cours']     ?? 0;
        $demandesAcceptees   = $statsParStatut['accepte']      ?? 0;
        $demandesImputees    = $statsParStatut['impute']       ?? 0;
        $demandesValides     = $statsParStatut['valide']       ?? 0;
        $demandesRejetees    = $statsParStatut['rejete']       ?? 0;
        $demandesTerminees   = $statsParStatut['termine']      ?? 0;
        $demandesCloturees   = $statsParStatut['cloture']      ?? 0;

        return view('umr.dashboard', compact(
            'totalDemandes',
            'statsParStatut',
            'demandesParMois',
            'pourcentagesParStatut',
            'donneesHistoriques',
            'travauxDebutes',
            'periodesAvenir',
            'mois',
            'annee',
            'demandesBrouillon',
            'demandesEnAttente',
            'demandesEnCours',
            'demandesAcceptees',
            'demandesImputees',
            'demandesValides',
            'demandesRejetees',
            'demandesTerminees',
            'demandesCloturees',
        ));
    }

    public function demandes()
    {
        $demandes = Demande::whereNotNull('umr_id')
            ->where('statut', 'impute')
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($demandes as $d) $d->statut = ucfirst(str_replace('_', ' ', $d->statut));
        return view('umr.demande', compact('demandes'));
    }

    public function demandesValidees()
    {
        $demandes = Demande::whereNotNull('umr_id')->where('statut', 'valide')
            ->with(['user', 'service', 'site'])->orderBy('updated_at', 'desc')->get();
        return view('umr.demandes_validees', compact('demandes'));
    }

    public function demandesDebutees()
    {
        $user = auth()->user();
        $demandes = Demande::where('umr_id', $user->id)->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'equipes', 'chefequipe', 'superviseur', 'executant'])
            ->orderBy('date_debut_intervention', 'desc')->get();
        return view('umr.demandes.debutees', compact('demandes'));
    }
}
