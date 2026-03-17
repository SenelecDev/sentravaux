<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($role = $request->get('role')) {
            $query->role($role);
        }

        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('nom')->paginate(20);
        $roles = Role::all();
        $services = User::whereNotNull('service')->where('service', '!=', '')->distinct()->orderBy('service')->pluck('service');

        return view('admin.users.index', compact('users', 'roles', 'services'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'required|string|unique:users,matricule',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'poste' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'service' => 'nullable|string|max:255',
            'direction' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['prenom'] . ' ' . $validated['nom'],
            'matricule' => strtoupper($validated['matricule']),
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'] ?? strtolower($validated['matricule']) . '@senelec.sn',
            'password' => Hash::make($validated['password']),
            'poste' => $validated['poste'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'service' => $validated['service'] ?? null,
            'direction' => $validated['direction'] ?? null,
            'departement' => $validated['departement'] ?? null,
            'organisation' => 'SENELEC',
            'is_active' => true,
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $user->load('roles');
        $user->loadCount('demandes');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $isLdap = $user->ldap_guid || $user->oracle_person_id;

        if ($isLdap) {
            // LDAP/Oracle users: only telephone, service + roles are editable
            $validated = $request->validate([
                'telephone' => 'nullable|string|max:20',
                'service' => 'nullable|string|max:255',
                'roles' => 'nullable|array',
            ]);

            $user->update([
                'telephone' => $validated['telephone'] ?? null,
                'service' => $validated['service'] ?? null,
            ]);

            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }
        } else {
            // Local users: full edit
            $validated = $request->validate([
                'matricule' => 'required|string|unique:users,matricule,' . $user->id,
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6|confirmed',
                'poste' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
                'service' => 'nullable|string|max:255',
                'direction' => 'nullable|string|max:255',
                'departement' => 'nullable|string|max:255',
                'is_active' => 'boolean',
                'roles' => 'nullable|array',
            ]);

            $user->update([
                'name' => $validated['prenom'] . ' ' . $validated['nom'],
                'matricule' => strtoupper($validated['matricule']),
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'poste' => $validated['poste'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'service' => $validated['service'] ?? null,
                'direction' => $validated['direction'] ?? null,
                'departement' => $validated['departement'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if ($validated['password'] ?? null) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
