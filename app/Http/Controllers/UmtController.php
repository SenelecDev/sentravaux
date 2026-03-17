<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\User;
use App\Models\Equipe;
use App\Models\Demande;
use App\Models\Personnel;
use App\Models\Service;
use App\Models\Site;
use App\Mail\DemandeCloture;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UmtController extends Controller
{
    public function index()
    {
        $umts = User::role("umt")->get();
        return view("umt.index", compact("umts"));
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}

    public function edit(string $id)
    {
        $demande = Demande::with([
            'service', 'site', 'equipes', 'chefequipe',
            'superviseur', 'executant', 'sad', 'terminatedBy'
        ])->find($id);

        $equipes = Equipe::all();
        $users = User::all();

        return view('umt.editer', compact('demande', 'equipes', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);
        $currentUser = auth()->user();

        // Une demande clôturée n'est plus modifiable via l'écran UMT
        if ($demande->statut === 'cloture') {
            return redirect()->route('umt.demandes.recues')->with('error', 'La demande ne peut plus être modifiée.');
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
        // Si la demande est terminée, seule l'action "cloturer" est autorisée
        if ($demande->statut === 'termine' && $action !== 'cloturer') {
            return redirect()->route('umt.demandes.recues')->with('error', 'La demande est terminée, seule la clôture est possible.');
        }

        // On ne force la saisie de team_type que pour l'action "dispatcher"
        if ($action === 'dispatcher') {
            $rules['team_type'] = 'required|in:interne,externe';
        } elseif ($request->has('team_type')) {
            // Dans les autres cas, si le champ est présent, on le valide simplement
            $rules['team_type'] = 'in:interne,externe';
        }

        if ($request->input('action') === 'terminer') {
            // Terminer (UMT ou chef d'équipe) : toujours exiger la date, les champs prestataire restent optionnels
            $rules['date_intervention'] = 'required|date';
            if ($request->filled('prestataire_nom')) {
                $rules['numero_commande'] = 'required|string';
            }
        }

        $request->validate($rules);

        $chefEquipeUserId = null;
        $superviseurUserId = null;
        $executantUserId = null;

        if ($request->filled('chef_equipe_id')) {
            $chefEquipe = User::find($request->chef_equipe_id);
            if (!$chefEquipe) return redirect()->back()->withErrors(['chef_equipe_id' => 'Chef d\'équipe invalide.']);
            $chefEquipeUserId = $chefEquipe->id;
        }

        if ($request->filled('superviseur_id')) {
            $superviseur = User::find($request->superviseur_id);
            if (!$superviseur) return redirect()->back()->withErrors(['superviseur_id' => 'Superviseur invalide.']);
            $superviseurUserId = $superviseur->id;
        } elseif ($request->filled('superviseur_externe_id')) {
            $superviseur = User::find($request->superviseur_externe_id);
            if (!$superviseur) return redirect()->back()->withErrors(['superviseur_externe_id' => 'Superviseur externe invalide.']);
            $superviseurUserId = $superviseur->id;
        }

        if ($request->filled('executant_id')) {
            $executant = User::find($request->executant_id);
            if (!$executant) return redirect()->back()->withErrors(['executant_id' => 'Exécutant invalide.']);
            $executantUserId = $executant->id;
        }

        $updateData = $request->except(['action']);

        // Mapper correctement le commentaire UMT (champ du formulaire => colonne comment_umt)
        if ($request->has('commentaire')) {
            $updateData['comment_umt'] = $request->input('commentaire');
            unset($updateData['commentaire']);
        }
        if ($chefEquipeUserId !== null) $updateData['chef_equipe_id'] = $chefEquipeUserId;
        if ($superviseurUserId !== null) $updateData['superviseur_id'] = $superviseurUserId;
        if ($executantUserId !== null) $updateData['executant_id'] = $executantUserId;

        $demande->update($updateData);

        // Gestion des équipes uniquement pour les actions "édition"
        // (sauvegarde simple, dispatch, début ou fin des travaux via le formulaire principal)
        if (in_array($action, [null, '', 'dispatcher', 'debut_travaux', 'terminer'], true)) {
            // Sync existing equipes (ne pas détacher en cas de simple clôture)
            if ($request->has('equipes')) {
                $equipes = [];
                $executantsEquipe = $request->input('executant_equipe', []);
                foreach ($request->input('equipes', []) as $index => $equipeId) {
                    if ($equipeId) {
                        $duree = $request->input('duree.' . $index);
                        $executantId = isset($executantsEquipe[$index]) ? $executantsEquipe[$index] : null;
                        if ($duree) {
                            $equipes[$equipeId] = ['duree' => $duree, 'executant_id' => $executantId];
                        }
                    }
                }
                $demande->equipes()->sync($equipes);
            } elseif (in_array($action, [null, ''], true)) {
                $demande->equipes()->detach();
            }

            // Handle new equipes (éviter les doublons sur le pivot)
            if ($request->has('equipes_noms_new') && $request->has('duree_new')) {
                $newEquipesNoms = $request->input('equipes_noms_new', []);
                $newDurees = $request->input('duree_new', []);
                $newExecutants = $request->input('executant_equipe_new', []);

                foreach ($newEquipesNoms as $index => $nomEquipe) {
                    if (!empty($nomEquipe) && isset($newDurees[$index]) && $newDurees[$index] > 0) {
                        $equipe = Equipe::where('nom', $nomEquipe)->first();
                        if ($equipe) {
                            $executantId = isset($newExecutants[$index]) && !empty($newExecutants[$index]) ? $newExecutants[$index] : null;

                            // Si l'équipe est déjà liée à la demande, on met à jour le pivot plutôt que de créer un doublon
                            if ($demande->equipes()->where('equipes.id', $equipe->id)->exists()) {
                                $demande->equipes()->updateExistingPivot($equipe->id, [
                                    'duree' => $newDurees[$index],
                                    'executant_id' => $executantId,
                                ]);
                            } else {
                                $demande->equipes()->attach($equipe->id, [
                                    'duree' => $newDurees[$index],
                                    'executant_id' => $executantId,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Handle actions (règles métier UMT / Chef d'équipe)
        $teamType = $request->input('team_type', $demande->team_type);

        switch ($request->input('action')) {
            case 'dispatcher':
                // Seul le chef d'unité (rôle UMT) peut dispatcher vers un chef d'équipe
                if (!$currentUser || !$currentUser->hasRole('umt')) {
                    return redirect()->back()->withErrors(['error' => 'Seul le chef d\'unité peut dispatcher la demande vers un chef d\'équipe.']);
                }
                // Comportement différent selon le type d'équipe
                if ($teamType === 'externe') {
                    // Prestataire externe : le superviseur externe est obligatoire, pas le chef d'équipe
                    if ($superviseurUserId === null) {
                        return redirect()->back()->withErrors([
                            'superviseur_externe_id' => 'Le superviseur doit être sélectionné pour dispatcher une prestation externe.',
                        ]);
                    }

                    $demande->update([
                        'statut' => 'valide',
                        'validated_by' => auth()->id(),
                        // $superviseur_id a déjà été positionné dans $updateData plus haut
                    ]);

                    return redirect()->route('umt.demandes.validees')->with('success', 'Demande validée pour prestataire externe.');
                } else {
                    // Équipe interne : chef d'équipe obligatoire
                    if ($chefEquipeUserId !== null) {
                        $demande->update([
                            'chef_equipe_id' => $chefEquipeUserId,
                            'statut' => 'valide',
                            'validated_by' => auth()->id()
                        ]);
                        $chefEquipe = User::find($chefEquipeUserId);
                        if ($chefEquipe) {
                            NotificationService::equipeAffectee($demande, $chefEquipe);
                        }
                        // Après validation & dispatch, rediriger vers la liste des demandes validées UMT
                        return redirect()->route('umt.demandes.validees')->with('success', 'Demande validée et transférée au chef d\'équipe.');
                    }
                    return redirect()->back()->withErrors(['chef_equipe_id' => 'Chef d\'équipe doit être sélectionné pour dispatcher.']);
                }

            case 'retour':
                // Retour au chef d'équipe : réservé au chef d'unité
                if (!$currentUser || !$currentUser->hasRole('umt')) {
                    return redirect()->back()->withErrors(['error' => 'Seul le chef d\'unité peut renvoyer la demande pour correction.']);
                }
                if ($demande->chef_equipe_id) {
                    $commentaireRetour = $request->input('commentaire_retour', '');
                    $messageRetour = 'Demande renvoyée pour correction par ' . auth()->user()->name . ' le ' . now()->format('d/m/Y à H:i');
                    if ($commentaireRetour) $messageRetour .= "\n\nCommentaire : " . $commentaireRetour;

                    $commentaireActuel = $demande->commentaire_equipe ?? '';
                    $nouveauCommentaire = $commentaireActuel
                        ? $commentaireActuel . "\n\n--- RETOUR POUR CORRECTION ---\n" . $messageRetour
                        : "--- RETOUR POUR CORRECTION ---\n" . $messageRetour;

                    $demande->update([
                        'statut' => 'valide',
                        'commentaire_equipe' => $nouveauCommentaire,
                    ]);

                    $chefEquipe = User::find($demande->chef_equipe_id);
                    if ($chefEquipe) {
                        NotificationService::demandeRetournee($demande, $chefEquipe, $commentaireRetour);
                    }
                    return redirect()->route('umt.demandes.recues')->with('success', 'Demande renvoyée au chef d\'équipe pour correction.');
                }
                return redirect()->back()->withErrors(['error' => 'Aucun chef d\'équipe assigné.']);

            case 'debut_travaux':
                // Début des travaux : chef d'unité OU chef d'équipe affecté
                if (
                    !$currentUser ||
                    !($currentUser->hasRole('umt') || $demande->chef_equipe_id === $currentUser->id)
                ) {
                    return redirect()->back()->withErrors(['error' => 'Seul le chef d\'unité ou le chef d\'équipe peut débuter les travaux.']);
                }
                $demande->update([
                    'statut' => 'en_cours',
                    'date_intervention' => now(),
                    'date_debut_intervention' => now(),
                ]);
                // Redirection selon le rôle
                if ($currentUser->hasRole('equipe')) {
                    return redirect()->route('equipe.demandes.debutees')->with('success', 'Début des travaux enregistré avec succès.');
                }
                return redirect()->route('umt.demandes.debutees')->with('success', 'Début des travaux enregistré avec succès.');

            case 'terminer':
                // Fin des travaux : chef d'unité OU chef d'équipe affecté
                if (
                    !$currentUser ||
                    !($currentUser->hasRole('umt') || $demande->chef_equipe_id === $currentUser->id)
                ) {
                    return redirect()->back()->withErrors(['error' => 'Seul le chef d\'unité ou le chef d\'équipe peut terminer les travaux.']);
                }
                $updateData = [
                    'statut' => 'termine',
                    'terminated_by' => auth()->id(),
                    'date_intervention' => $request->date_intervention,
                ];
                if ($request->filled('prestataire_nom')) $updateData['prestataire_nom'] = $request->prestataire_nom;
                if ($request->filled('numero_commande')) $updateData['numero_commande'] = $request->numero_commande;
                if ($request->filled('commentaire_prestataire')) $updateData['commentaire_prestataire'] = $request->commentaire_prestataire;
                if (!$demande->date_fin) $updateData['date_fin'] = now();

                $demande->update($updateData);
                // Redirection selon le rôle
                if ($currentUser->hasRole('equipe')) {
                    return redirect()->route('equipe.demandes.terminees')->with('success', 'Demande terminée avec succès.');
                }
                return redirect()->route('umt.demandes.terminees')->with('success', 'Demande terminée avec succès.');

            case 'cloturer':
                // Clôture : strictement réservée au chef d'unité
                if (!$currentUser || !$currentUser->hasRole('umt')) {
                    return redirect()->back()->withErrors(['error' => 'Seul le chef d\'unité peut clôturer la demande.']);
                }
                $demande->update(['statut' => 'cloture', 'cloture_par' => auth()->id()]);

                try {
                    // N1 : Approbateur
                    $n1 = $demande->approvedBy ?: $demande->approbateurN1;

                    // N2 : Chef de service (SAD ou SEG)
                    $n2 = $demande->sad ?: $demande->seg;

                    // N3 : Chef d'unité (UMT / UBT / UNSP / UMR / UTGC)
                    $n3 = null;
                    $n3Name = null;

                    foreach (['umt', 'ubt', 'unsp', 'umr', 'utgc'] as $unite) {
                        $field = $unite . '_id';
                        if ($demande->$field) {
                            $n3 = User::find($demande->$field);
                            $n3Name = $demande->$unite ? $demande->$unite->name : null;
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

                    $output = $pdf->output();
                    $pdfPath = 'demandes/' . $demande->numero_demande . '.pdf';
                    Storage::put('public/' . $pdfPath, $output);

                    $demandeur = $demande->user;
                    $clotureName = null;
                    foreach (['unsp', 'ubt', 'umt', 'umr', 'utgc'] as $unite) {
                        if ($demande->$unite) {
                            $clotureName = $demande->$unite->name;
                            break;
                        }
                    }

                    Mail::to($demandeur->email)->send(new DemandeCloture($demande, $pdfPath, $clotureName));
                } catch (\Throwable $e) {
                    Log::error('Erreur lors de la génération/envoi du PDF de clôture', [
                        'demande_id' => $demande->id,
                        'message' => $e->getMessage(),
                    ]);
                }
                return redirect()->route('umt.demandes.recues')->with('success', 'Demande clôturée avec succès.');

            default:
                return redirect()->route('umt.demandes.recues')->with('success', 'Demande mise à jour avec succès.');
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
            ->where(function ($query) use ($user) {
                // Toutes les demandes rattachées à l'unité UMT courante,
                // qu'elles aient été terminées par le chef d'unité ou par le chef d'équipe
                $query->where('umt_id', $user->id)
                      ->orWhereHas('umt', fn($q) => $q->where('id', $user->id));
            })
            ->with(['user', 'site', 'service', 'equipes', 'chefequipe', 'superviseur', 'executant', 'terminatedBy', 'validatedBy', 'umt'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('umt.demandes.terminees', compact('demandes'));
    }

    public function demandesCloturees()
    {
        $user = auth()->user();

        $demandes = Demande::where('statut', 'cloture')
            ->where(function ($query) use ($user) {
                $query->where('umt_id', $user->id)
                      ->orWhereHas('umt', fn($q) => $q->where('id', $user->id));
            })
            ->with(['user', 'site', 'service', 'equipes', 'chefequipe', 'superviseur', 'executant', 'terminatedBy', 'cloturedBy', 'validatedBy', 'umt'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('umt.demandes.cloturees', compact('demandes'));
    }

    public function destroy(string $id) {}

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));

        $baseQuery = fn() => Demande::where(function ($query) use ($user) {
            $query->whereNotNull('umt_id')->orWhere('umt_id', $user->id);
        });

        $totalDemandes = $baseQuery()->count();
        $statuts = ['brouillon', 'en_attente', 'en_cours', 'accepte', 'rejete', 'valide', 'impute', 'termine', 'cloture'];

        $demandesBrouillon = $baseQuery()->where('statut', 'brouillon')->count();
        $demandesEnAttente = $baseQuery()->where('statut', 'en_attente')->count();
        $demandesEnCours   = $baseQuery()->where('statut', 'en_cours')->count();
        $demandesAcceptees = $baseQuery()->where('statut', 'accepte')->count();
        $demandesRejetees  = $baseQuery()->where('statut', 'rejete')->count();
        $demandesValides   = $baseQuery()->where('statut', 'valide')->count();
        $demandesImputees  = $baseQuery()->where('statut', 'impute')->count();
        $demandesTerminees = $baseQuery()->where('statut', 'termine')->count();
        $demandesCloturees = $baseQuery()->where('statut', 'cloture')->count();

        $demandesParMois = [];
        foreach ($statuts as $statut) {
            $demandesParMois[$statut] = $baseQuery()->where('statut', $statut)
                ->whereMonth('created_at', $mois)->whereYear('created_at', $annee)->count();
        }

        $totalParMois = array_sum($demandesParMois);
        $pourcentagesParStatut = [];
        if ($totalParMois > 0) {
            foreach ($demandesParMois as $statut => $count) {
                $pourcentagesParStatut[$statut] = ($count / $totalParMois) * 100;
            }
        } else {
            $pourcentagesParStatut = array_fill_keys(array_keys($demandesParMois), 0);
        }

        $donneesHistoriques = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $donneesHistoriques['mois'][] = $date->format('M Y');
            foreach (array_keys($demandesParMois) as $statut) {
                $donneesHistoriques[$statut][] = $baseQuery()->where('statut', $statut)
                    ->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
            }
        }

        $travauxDebutes = Demande::where('umt_id', $user->id)
            ->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'chefequipe'])
            ->orderBy('date_debut_intervention', 'desc')->limit(10)->get();

        $periodesAvenir = Demande::where('umt_id', $user->id)
            ->whereNotNull('date_debut_intervention')->whereNotNull('date_fin_intervention')
            ->where('date_debut_intervention', '>=', now())
            ->where('date_debut_intervention', '<=', now()->addDays(30))
            ->whereNotIn('statut', ['cloture', 'termine', 'en_cours'])
            ->with(['user', 'site', 'service'])
            ->orderBy('date_debut_intervention', 'asc')->limit(10)->get();

        return view('umt.dashboard', compact(
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
            'donneesHistoriques',
            'travauxDebutes',
            'periodesAvenir',
            'mois',
            'annee'
        ));
    }

    public function demandes()
    {
        $demandes = Demande::whereNotNull('umt_id')
            ->where('statut', 'impute')
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($demandes as $demande) {
            $demande->statut = ucfirst(str_replace('_', ' ', $demande->statut));
        }
        return view('umt.demande', compact('demandes'));
    }

    public function demandesValidees()
    {
        $demandes = Demande::whereNotNull('umt_id')->where('statut', 'valide')
            ->with(['user', 'service', 'site'])->orderBy('updated_at', 'desc')->get();
        return view('umt.demandes_validees', compact('demandes'));
    }

    public function demandesRejetees()
    {
        $demandes = Demande::whereNotNull('umt_id')
            ->where('statut', 'rejete')
            ->whereHas('rejectedBy', function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('name', 'umt');
                });
            })
            ->with(['user', 'service', 'site', 'rejectedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('umt.demandes_rejetees', compact('demandes'));
    }

    public function demandesDebutees()
    {
        $user = auth()->user();
        $demandes = Demande::where('umt_id', $user->id)->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'equipes', 'chefequipe', 'superviseur', 'executant', 'umt'])
            ->orderBy('date_debut_intervention', 'desc')->get();
        return view('umt.demandes.debutees', compact('demandes'));
    }
}
