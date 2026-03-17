<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string',
            'password' => 'required|string',
        ]);

        $matricule = strtoupper(trim($request->matricule));
        $password = $request->password;

        // 1) Authentification locale
        $user = User::where('matricule', $matricule)->first();

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors(['matricule' => 'Votre compte est désactivé. Contactez l\'administrateur.']);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->update(['last_activity_at' => now()]);

            // Auto-assigner le rôle demandeur si aucun rôle
            if ($user->roles->isEmpty()) {
                $user->assignRole('demandeur');
            }

            return $this->redirectByRole($user);
        }

        // 2) Authentification LDAP (si activée)
        if (config('app.ldap_enabled', false)) {
            try {
                $ldapUser = $this->attemptLdapAuth($matricule, $password);
                if ($ldapUser) {
                    Auth::login($ldapUser, $request->boolean('remember'));
                    $request->session()->regenerate();
                    $ldapUser->update(['last_activity_at' => now()]);

                    // Auto-assigner le rôle demandeur si aucun rôle
                    if ($ldapUser->roles->isEmpty()) {
                        $ldapUser->assignRole('demandeur');
                    }

                    return $this->redirectByRole($ldapUser);
                }
            } catch (\Exception $e) {
                Log::error('LDAP Auth Error: ' . $e->getMessage());
            }
        }

        return back()->withErrors([
            'matricule' => 'Identifiants incorrects.',
        ])->withInput($request->only('matricule'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function attemptLdapAuth(string $matricule, string $password): ?User
    {
        // Recherche LDAP par samaccountname
        $ldapRecord = \LdapRecord\Models\ActiveDirectory\User::findBy('samaccountname', $matricule);

        if (!$ldapRecord) {
            return null;
        }

        // Tentative de bind
        try {
            $connection = \LdapRecord\Container::getDefaultConnection();
            if (!$connection->auth()->attempt($ldapRecord->getDn(), $password)) {
                return null;
            }
        } catch (\Exception $e) {
            Log::error('LDAP Bind failed: ' . $e->getMessage());
            return null;
        }

        // Sync ou création de l'utilisateur local
        $user = User::where('matricule', $matricule)->first();

        if (!$user) {
            $user = User::create([
                'name' => $ldapRecord->getFirstAttribute('displayname') ?? $matricule,
                'email' => $ldapRecord->getFirstAttribute('mail') ?? $matricule . '@senelec.sn',
                'matricule' => $matricule,
                'ldap_username' => $ldapRecord->getFirstAttribute('samaccountname'),
                'ldap_guid' => $ldapRecord->getConvertedGuid(),
                'nom' => $ldapRecord->getFirstAttribute('sn'),
                'prenom' => $ldapRecord->getFirstAttribute('givenname'),
                'poste' => $ldapRecord->getFirstAttribute('title'),
                'service' => $ldapRecord->getFirstAttribute('department'),
                'telephone' => $ldapRecord->getFirstAttribute('mobile'),
                'organisation' => $ldapRecord->getFirstAttribute('company'),
                'password' => Hash::make($password),
                'is_active' => true,
                'last_sync_at' => now(),
            ]);

            // Rôle par défaut
            $user->assignRole('demandeur');
        } else {
            // Mise à jour sync
            $user->update([
                'ldap_guid' => $ldapRecord->getConvertedGuid(),
                'last_sync_at' => now(),
            ]);
        }

        return $user;
    }

    protected function redirectByRole(User $user): \Illuminate\Http\RedirectResponse
    {
        // Ordre de priorité : rôle le plus élevé en premier
        $roleRoutes = [
            'admin'       => 'admin.dashboard',
            'dage'        => 'dage.dashboard',
            'sad'         => 'sad.dashboard',
            'seg'         => 'seg.dashboard',
            'approbateur' => 'approbateur.dashboard',
            'umt'         => 'umt.dashboard',
            'ubt'         => 'ubt.dashboard',
            'unsp'        => 'unsp.dashboard',
            'umr'         => 'umr.dashboard',
            'utgc'        => 'utgc.dashboard',
            'equipe'      => 'equipe.dashboard',
            'demandeur'   => 'demandeur.dashboard',
        ];

        foreach ($roleRoutes as $role => $route) {
            if ($user->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        // Fallback
        return redirect()->route('dashboard');
    }
}
