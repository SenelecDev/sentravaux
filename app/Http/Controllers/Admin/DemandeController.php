<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
    public function index(Request $request)
    {
        $query = Demande::with(['user', 'user.roles']);

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche par numéro ou objet
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'like', "%{$search}%")
                  ->orWhere('objet', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('nom', 'like', "%{$search}%")
                         ->orWhere('prenom', 'like', "%{$search}%")
                         ->orWhere('matricule', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par nature
        if ($request->filled('nature')) {
            $query->where('nature', $request->nature);
        }

        $demandes = $query->orderByDesc('created_at')->paginate(20);

        $statuts = Demande::select('statut')->distinct()->whereNotNull('statut')->pluck('statut');
        $natures = Demande::select('nature')->distinct()->whereNotNull('nature')->pluck('nature');

        return view('admin.demandes.index', compact('demandes', 'statuts', 'natures'));
    }

    public function show(Demande $demande)
    {
        $demande->load(['user', 'user.roles']);
        return view('admin.demandes.show', compact('demande'));
    }
}
