<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Equipe;
use App\Models\Demande;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EquipeController extends Controller
{
    public function index()
    {
        $equipes = Equipe::all();
        return view("equipe.index", compact("equipes"));
    }

    public function create()
    {
        return view('equipe.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Equipe::create($data);

        return redirect()
            ->route('equipe.index')
            ->with('success', 'Équipe créée avec succès.');
    }
    public function show(string $id) {}

    public function edit(string $id)
    {
        $user = auth()->user();

        // Mode chef d'équipe : édition d'une demande
        if ($user && $user->hasRole('equipe')) {
            $demande = Demande::with([
                'service', 'site', 'equipes', 'chefequipe',
                'superviseur', 'executant', 'validatedBy', 'terminatedBy'
            ])->find($id);

            $equipes = Equipe::all();
            $users = User::all();

            // Réutilise le même template moderne que pour l'UMT
            return view('umt.editer', compact('demande', 'equipes', 'users'));
        }

        // Mode admin : édition d'une équipe (table equipes)
        $equipe = Equipe::findOrFail($id);
        return view('equipe.edit', compact('equipe'));
    }

    public function update(Request $request, string $id)
    {
        $user = auth()->user();

        // Mode chef d'équipe : mise à jour d'une demande
        if ($user && $user->hasRole('equipe')) {
            $request->validate([
                'team_type' => 'required|in:interne,externe',
                'chef_equipe_id' => 'nullable|exists:users,id',
                'superviseur_id' => 'nullable|exists:users,id',
                'executant_id' => 'nullable|exists:users,id',
                'equipes_ids' => 'array',
                'duree' => 'array',
                'executant_equipe.*' => 'nullable|exists:users,id',
                'equipes_noms_new' => 'array',
                'duree_new' => 'array',
                'executant_equipe_new.*' => 'nullable|exists:users,id',
                'commentaire_equipe' => 'nullable|string',
                'prestataire_nom' => 'nullable|string',
            ]);

            $demande = Demande::findOrFail($id);

            if (in_array($demande->statut, ['termine', 'cloture'])) {
                return redirect()->route('equipe.demandes.recues')->with('error', 'La demande ne peut plus être modifiée.');
            }

            $updateData = $request->except(['action', 'equipes', 'duree', 'executant_equipe', 'equipes_noms_new', 'duree_new', 'executant_equipe_new']);

            if ($request->filled('chef_equipe_id')) $updateData['chef_equipe_id'] = $request->chef_equipe_id;
            if ($request->filled('superviseur_id')) $updateData['superviseur_id'] = $request->superviseur_id;
            if ($request->filled('executant_id')) $updateData['executant_id'] = $request->executant_id;

            $demande->update($updateData);

            // Sync existing equipes
            if ($request->has('equipes')) {
                $equipesData = [];
                foreach ($request->input('equipes', []) as $index => $equipeId) {
                    if ($equipeId) {
                        $equipesData[$equipeId] = [
                            'duree' => $request->input('duree.' . $index, 1),
                            'executant_id' => $request->input('executant_equipe.' . $index)
                        ];
                    }
                }
                $demande->equipes()->sync($equipesData);
            }

            // Handle new equipes
            if ($request->has('equipes_noms_new')) {
                foreach ($request->input('equipes_noms_new', []) as $index => $equipeNom) {
                    if (!empty($equipeNom)) {
                        $equipe = Equipe::firstOrCreate(['nom' => $equipeNom]);
                        $demande->equipes()->attach($equipe->id, [
                            'duree' => $request->input('duree_new.' . $index, 1),
                            'executant_id' => $request->input('executant_equipe_new.' . $index)
                        ]);
                    }
                }
            }

            switch ($request->input('action')) {
                case 'dispatcher':
                    $demande->update(['date_intervention' => now()]);
                    return redirect()->route('equipe.demandes.recues')->with('success', 'Demande des travaux exécutée avec succès.');

                case 'terminer':
                    $demande->update([
                        'statut' => 'termine',
                        'date_fin' => now(),
                        'terminated_by' => auth()->id()
                    ]);
                    return redirect()->route('equipe.demandes.recues')->with('success', 'Demande de travaux terminée avec succès.');

                default:
                    return redirect()->route('equipe.demandes.recues')->with('success', 'Demande mise à jour avec succès.');
            }
        }

        // Mode admin : mise à jour d'une équipe (table equipes)
        $equipe = Equipe::findOrFail($id);

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $equipe->update($data);

        return redirect()
            ->route('equipe.index')
            ->with('success', 'Équipe mise à jour avec succès.');
    }

    public function destroy(string $id) {}

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $mois = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));

        $totalDemandes = Demande::where('chef_equipe_id', $user->id)
            ->whereIn('statut', ['valide', 'termine', 'cloture'])->count();

        $demandesValides = Demande::where('chef_equipe_id', $user->id)->where('statut', 'valide')->count();
        $demandesTerminees = Demande::where('chef_equipe_id', $user->id)->where('statut', 'termine')->count();
        $demandesCloturees = Demande::where('chef_equipe_id', $user->id)->where('statut', 'cloture')->count();

        $demandesParMois = [
            'valide' => Demande::where('chef_equipe_id', $user->id)->where('statut', 'valide')
                ->whereMonth('created_at', $mois)->whereYear('created_at', $annee)->count(),
            'termine' => Demande::where('chef_equipe_id', $user->id)->where('statut', 'termine')
                ->whereMonth('created_at', $mois)->whereYear('created_at', $annee)->count(),
            'cloture' => Demande::where('chef_equipe_id', $user->id)->where('statut', 'cloture')
                ->whereMonth('created_at', $mois)->whereYear('created_at', $annee)->count(),
        ];

        $totalParMois = array_sum($demandesParMois);
        $pourcentagesParStatut = $totalParMois > 0
            ? array_map(fn($c) => ($c / $totalParMois) * 100, $demandesParMois)
            : array_fill_keys(array_keys($demandesParMois), 0);

        $donneesHistoriques = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $donneesHistoriques['mois'][] = $date->format('M Y');
            foreach (array_keys($demandesParMois) as $statut) {
                $donneesHistoriques[$statut][] = Demande::where('chef_equipe_id', $user->id)
                    ->where('statut', $statut)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)->count();
            }
        }

        return view('equipe.dashboard', compact(
            'totalDemandes', 'demandesValides', 'demandesTerminees', 'demandesCloturees',
            'demandesParMois', 'pourcentagesParStatut', 'donneesHistoriques', 'mois', 'annee'
        ));
    }

    public function demandes()
    {
        $user = auth()->user();
        $demandes = Demande::where('chef_equipe_id', $user->id)
            ->with([
                'user', 'approbateurN1', 'images', 'service', 'site',
                'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy',
                'equipes', 'chefequipe', 'superviseur', 'executant',
                'sad', 'seg', 'umt', 'umr', 'utgc', 'ubt', 'unsp'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($demandes as $d) {
            $d->statut = ucfirst(str_replace('_', ' ', $d->statut));
        }

        return view('equipe.demande', compact('demandes'));
    }

    public function demandes_terminees()
    {
        $user = auth()->user();
        $demandes = Demande::where('chef_equipe_id', $user->id)
            ->where('statut', 'termine')
            ->get();
        foreach ($demandes as $d) {
            $d->statut = ucfirst(str_replace('_', ' ', $d->statut));
        }
        return view('equipe.demande', compact('demandes'));
    }

    public function demandes_a_traiter()
    {
        $user = auth()->user();
        $demandes = Demande::where('chef_equipe_id', $user->id)
            ->where('statut', 'valide')
            ->with(['user', 'service', 'site'])
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($demandes as $d) {
            $d->statut = ucfirst(str_replace('_', ' ', $d->statut));
        }

        return view('equipe.demande', compact('demandes'));
    }

    public function demandes_debutees()
    {
        $user = auth()->user();
        $demandes = Demande::where('chef_equipe_id', $user->id)
            ->where('statut', 'en_cours')
            ->with(['user', 'service', 'site'])
            ->orderBy('date_debut_intervention', 'desc')
            ->get();

        foreach ($demandes as $d) {
            $d->statut = ucfirst(str_replace('_', ' ', $d->statut));
        }

        return view('equipe.demande', compact('demandes'));
    }

    public function demandes_cloturees()
    {
        $user = auth()->user();
        $demandes = Demande::where('chef_equipe_id', $user->id)
            ->where('statut', 'cloture')
            ->with(['user', 'service', 'site'])
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($demandes as $d) {
            $d->statut = ucfirst(str_replace('_', ' ', $d->statut));
        }

        return view('equipe.demande', compact('demandes'));
    }
}
