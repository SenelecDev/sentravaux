<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Equipe;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UalController extends Controller
{
    public function index() { return view('ual.index'); }
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function destroy(string $id) {}

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));
        $teamType = $request->get('team_type');

        $baseQuery = fn() => Demande::where(function ($q) use ($user) {
            $q->whereNotNull('ual_id')->orWhere('ual_id', $user->id);
        })->when(in_array($teamType, ['interne', 'externe'], true), fn($q) => $q->where('team_type', $teamType));

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

        $travauxDebutes = Demande::where('ual_id', $user->id)->where('statut', 'en_cours')
            ->with(['user', 'site', 'service', 'chefequipe'])->orderBy('date_debut_intervention', 'desc')->limit(10)->get();

        $periodesAvenir = Demande::where('ual_id', $user->id)
            ->whereNotNull('date_debut_intervention')->whereNotNull('date_fin_intervention')
            ->where('date_debut_intervention', '>=', now())->where('date_debut_intervention', '<=', now()->addDays(30))
            ->whereNotIn('statut', ['cloture', 'termine', 'en_cours'])
            ->with(['user', 'site', 'service'])->orderBy('date_debut_intervention', 'asc')->limit(10)->get();

        $demandesBrouillon = $statsParStatut['brouillon'] ?? 0;
        $demandesEnAttente = $statsParStatut['en_attente'] ?? 0;
        $demandesEnCours = $statsParStatut['en_cours'] ?? 0;
        $demandesAcceptees = $statsParStatut['accepte'] ?? 0;
        $demandesImputees = $statsParStatut['impute'] ?? 0;
        $demandesValides = $statsParStatut['valide'] ?? 0;
        $demandesRejetees = $statsParStatut['rejete'] ?? 0;
        $demandesTerminees = $statsParStatut['termine'] ?? 0;
        $demandesCloturees = $statsParStatut['cloture'] ?? 0;

        return view('ual.dashboard', compact(
            'totalDemandes',
            'statsParStatut',
            'demandesParMois',
            'pourcentagesParStatut',
            'donneesHistoriques',
            'travauxDebutes',
            'periodesAvenir',
            'mois',
            'annee',
            'teamType',
            'demandesBrouillon',
            'demandesEnAttente',
            'demandesEnCours',
            'demandesAcceptees',
            'demandesImputees',
            'demandesValides',
            'demandesRejetees',
            'demandesTerminees',
            'demandesCloturees'
        ));
    }

    public function demandes() { return $this->listByStatus(null, 'Demandes recues UAL'); }
    public function demandesValidees() { return $this->listByStatus('valide', 'Demandes validees UAL'); }
    public function demandesDebutees() { return $this->listByStatus('en_cours', 'Travaux debutes UAL'); }
    public function demandesTerminees() { return $this->listByStatus('termine', 'Demandes terminees UAL'); }
    public function demandesCloturees() { return $this->listByStatus('cloture', 'Demandes cloturees UAL'); }

    public function edit(string $id)
    {
        $demande = Demande::with(['service', 'site', 'equipes', 'chefequipe', 'superviseur', 'executant'])->findOrFail($id);
        $users = User::all();
        $chefEquipeUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'equipe');
        })->whereDoesntHave('roles', function ($query) {
            $query->where('name', '!=', 'equipe');
        })->orderBy('name')->get();
        return view('ual.editer', ['demande' => $demande, 'equipes' => Equipe::all(), 'users' => $users, 'chefEquipeUsers' => $chefEquipeUsers]);
    }

    public function update(Request $request, string $id)
    {
        $demande = Demande::findOrFail($id);
        $action = $request->input('action');

        if ($demande->statut === 'cloture') {
            return redirect()->route('ual.demandes.recues')->with('error', 'La demande ne peut plus être modifiée.');
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
            'numero_commande' => ['nullable', 'string', 'max:255', 'regex:/^[^,;]+$/', Rule::unique('demandes', 'numero_commande')->ignore($demande->id)],
            'commentaire_prestataire' => 'nullable|string',
        ];

        if ($action === 'dispatcher') {
            $rules['team_type'] = 'required|in:interne,externe';
        } elseif ($request->has('team_type')) {
            $rules['team_type'] = 'in:interne,externe';
        }
        if ($action === 'terminer') {
            $rules['date_intervention'] = 'required|date';
            if ($request->filled('prestataire_nom')) $rules['numero_commande'] = ['required', 'string', 'max:255', 'regex:/^[^,;]+$/', Rule::unique('demandes', 'numero_commande')->ignore($demande->id)];
        }
        $request->validate($rules, [
            'numero_commande.regex' => 'Un seul numero de commande est autorise par demande (pas de liste).',
            'numero_commande.unique' => 'Ce numero de commande est deja utilise sur une autre demande.',
        ]);

        $chefEquipeUserId = $request->filled('chef_equipe_id') ? $request->chef_equipe_id : null;
        $superviseurUserId = $request->filled('superviseur_id') ? $request->superviseur_id : ($request->filled('superviseur_externe_id') ? $request->superviseur_externe_id : null);
        $executantUserId = $request->filled('executant_id') ? $request->executant_id : null;

        $updateData = $request->except(['action', 'commentaire']);
        if ($request->has('commentaire')) {
            $updateData['commentaire_equipe'] = $request->input('commentaire');
        }
        if ($chefEquipeUserId) $updateData['chef_equipe_id'] = $chefEquipeUserId;
        if ($superviseurUserId) $updateData['superviseur_id'] = $superviseurUserId;
        if ($executantUserId) $updateData['executant_id'] = $executantUserId;
        $demande->update($updateData);

        if (in_array($action, [null, '', 'dispatcher', 'terminer'], true)) {
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
        }

        $teamType = $request->input('team_type', $demande->team_type);
        switch ($action) {
            case 'dispatcher':
                if ($teamType === 'externe') {
                    if (!$superviseurUserId) {
                        return redirect()->back()->withErrors(['superviseur_externe_id' => 'Le superviseur doit être sélectionné pour dispatcher une prestation externe.']);
                    }
                    $demande->update(['statut' => 'valide', 'validated_by' => auth()->id()]);
                    NotificationService::handleWorkflowAction($demande->fresh(), 'dispatcher_externe');
                    return redirect()->route('ual.demandes.validees')->with('success', 'Demande validée pour prestataire externe.');
                }
                if ($chefEquipeUserId) {
                    $demande->update(['chef_equipe_id' => $chefEquipeUserId, 'statut' => 'valide', 'validated_by' => auth()->id()]);
                    $chefEquipe = User::find($chefEquipeUserId);
                    if ($chefEquipe) NotificationService::equipeAffectee($demande, $chefEquipe);
                    return redirect()->route('ual.demandes.validees')->with('success', 'Demande validée et transférée au chef d\'équipe.');
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
                    return redirect()->route('ual.demandes.recues')->with('success', 'Demande renvoyée au chef d\'équipe.');
                }
                return redirect()->back()->withErrors(['error' => 'Aucun chef d\'équipe assigné.']);

            case 'debut_travaux':
                $demande->update(['statut' => 'en_cours', 'date_intervention' => now(), 'date_debut_intervention' => now()]);
                NotificationService::handleWorkflowAction($demande->fresh(), 'debut_travaux');
                return redirect()->route('ual.demandes.debutees')->with('success', 'Début des travaux enregistré.');

            case 'terminer':
                $termData = ['statut' => 'termine', 'terminated_by' => auth()->id(), 'date_intervention' => $request->date_intervention];
                if ($request->filled('prestataire_nom')) $termData['prestataire_nom'] = $request->prestataire_nom;
                if ($request->filled('numero_commande')) $termData['numero_commande'] = $request->numero_commande;
                if ($request->filled('commentaire_prestataire')) $termData['commentaire_prestataire'] = $request->commentaire_prestataire;
                if (!$demande->date_fin) $termData['date_fin'] = now();
                $demande->update($termData);
                NotificationService::handleWorkflowAction($demande->fresh(), 'terminer');
                return redirect()->route('ual.demandes.terminees')->with('success', 'Demande terminée avec succès.');

            case 'cloturer':
                $demande->update(['statut' => 'cloture', 'cloture_par' => auth()->id(), 'date_cloture' => now()]);
                NotificationService::handleWorkflowAction($demande->fresh(), 'cloturer');
                return redirect()->route('ual.demandes.recues')->with('success', 'Demande clôturée avec succès.');

            default:
                return redirect()->route('ual.demandes.recues')->with('success', 'Demande mise a jour.');
        }
    }

    private function listByStatus(?string $statut, string $title)
    {
        $query = Demande::whereNotNull('ual_id')->with(['user', 'site', 'service']);
        if ($statut) $query->where('statut', $statut);
        $demandes = $query->latest('updated_at')->get();
        return view('ual.demande', compact('demandes', 'title'));
    }
}

