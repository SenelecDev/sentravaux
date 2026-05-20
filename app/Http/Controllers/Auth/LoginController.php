<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OracleHRService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class LoginController extends Controller
{
    protected OracleHRService $oracleService;
    protected ?User $lastSyncedUser = null;

    public function __construct(OracleHRService $oracleService)
    {
        $this->oracleService = $oracleService;
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string',
            'password' => 'required|string',
        ], [
            'matricule.required' => 'Le matricule est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $matricule = strtoupper(trim((string) $request->input('matricule')));
        $password = (string) $request->input('password');

        Log::info('Login attempt', ['matricule' => $matricule]);

        // 1) Authentification locale en priorité
        $user = User::whereRaw('UPPER(matricule) = ?', [$matricule])->first();

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors(['matricule' => 'Votre compte est désactivé. Contactez l\'administrateur.']);
            }

            Log::info('Local authentication successful', ['matricule' => $matricule]);
            return $this->handleSuccessfulLogin($request, $matricule);
        }

        // 2) Authentification LDAP (si activée)
        if (config('app.ldap_enabled', false)) {
            if ($this->authenticateViaLdap($matricule, $password)) {
                return $this->handleSuccessfulLogin($request, $matricule);
            }
        }

        Log::warning('Login failed', ['matricule' => $matricule]);

        return back()->withErrors([
            'matricule' => 'Identifiants incorrects. Veuillez verifier votre matricule et mot de passe.',
        ])->withInput($request->only('matricule'));
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out', ['user_id' => $userId]);

        return redirect()->route('login')
            ->with('success', 'Vous avez ete deconnecte avec succes.');
    }

    protected function authenticateViaLdap(string $matricule, string $password): bool
    {
        try {
            $connection = Container::getDefaultConnection();

            // 1) Recherche directe par samaccountname / employeenumber
            $ldapUser = LdapUser::where('samaccountname', '=', $matricule)
                ->orWhere('employeenumber', '=', $matricule)
                ->first();

            // 2) Fallback sur company (ex: "SENELEC 12345")
            if (!$ldapUser) {
                $ldapUser = LdapUser::whereContains('company', $matricule)->first();
            }

            // 3) Fallback Oracle -> email -> LDAP
            if (!$ldapUser && env('ORACLE_ENABLED', false)) {
                $ldapUser = $this->findLdapUserViaOracle($matricule);
            }

            if (!$ldapUser) {
                Log::info('LDAP user not found', ['matricule' => $matricule]);
                return false;
            }

            if (!$connection->auth()->attempt($ldapUser->getDn(), $password)) {
                Log::info('LDAP bind failed - wrong password', ['matricule' => $matricule]);
                return false;
            }

            $this->syncUserFromLdap($ldapUser, $matricule);

            Log::info('LDAP authentication successful', ['matricule' => $matricule]);
            return true;
        } catch (\Exception $e) {
            Log::error('LDAP authentication error', [
                'matricule' => $matricule,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function findLdapUserViaOracle(string $matricule): ?LdapUser
    {
        try {
            $oracleData = $this->oracleService->getEmployeeByMatricule($matricule);
            if (!$oracleData) {
                return null;
            }

            $email = $oracleData['email'] ?? null;
            if (empty($email)) {
                return null;
            }

            return LdapUser::where('mail', '=', $email)->first();
        } catch (\Exception $e) {
            Log::warning('Oracle lookup failed', [
                'matricule' => $matricule,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function syncUserFromLdap(LdapUser $ldapUser, string $matricule): User
    {
        $actualMatricule = $this->extractMatriculeFromLdap($ldapUser, $matricule);
        $ldapEmail = $ldapUser->getFirstAttribute('mail');

        $user = User::whereRaw('UPPER(matricule) = ?', [strtoupper($actualMatricule)])
            ->orWhere('ldap_guid', $ldapUser->getConvertedGuid())
            ->first();

        if (!$user && $ldapEmail) {
            $user = User::where('email', $ldapEmail)->first();
        }

        if (!$user) {
            $user = new User();
            $user->password = Hash::make('password');
            $user->is_active = true;
        }

        $user->matricule = strtoupper($actualMatricule);
        $user->nom = (string) ($ldapUser->getFirstAttribute('sn') ?? $user->nom ?? '');
        $user->prenom = (string) ($ldapUser->getFirstAttribute('givenname') ?? $user->prenom ?? '');
        $user->name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
        if ($user->name === '') {
            $user->name = (string) ($ldapUser->getFirstAttribute('displayname') ?? strtoupper($actualMatricule));
        }
        $user->email = (string) ($ldapEmail ?? ($actualMatricule . '@senelec.sn'));
        $user->poste = $ldapUser->getFirstAttribute('title') ?? $user->poste;
        $user->service = $ldapUser->getFirstAttribute('department') ?? $user->service;
        $user->organisation = $ldapUser->getFirstAttribute('company') ?? $user->organisation;
        $user->telephone = $ldapUser->getFirstAttribute('telephonenumber') ?? $ldapUser->getFirstAttribute('mobile') ?? $user->telephone;
        $user->ldap_username = $ldapUser->getFirstAttribute('samaccountname');
        $user->ldap_guid = $ldapUser->getConvertedGuid();
        $user->entreprise = 'SENELEC';
        $user->last_sync_at = now();

        $thumbnailPhoto = $ldapUser->getFirstAttribute('thumbnailphoto');
        if ($thumbnailPhoto) {
            $photoPath = $this->saveProfilePhoto((string) $thumbnailPhoto, $actualMatricule);
            if ($photoPath) {
                $user->photo = $photoPath;
            }
        }

        $user->save();

        if (env('ORACLE_ENABLED', false)) {
            $this->syncWithOracle($user);
        }

        $this->lastSyncedUser = $user;
        return $user;
    }

    protected function extractMatriculeFromLdap(LdapUser $ldapUser, string $fallback): string
    {
        $employeeNumber = $ldapUser->getFirstAttribute('employeenumber');
        if (!empty($employeeNumber)) {
            return strtoupper((string) $employeeNumber);
        }

        $company = (string) ($ldapUser->getFirstAttribute('company') ?? '');
        if ($company !== '') {
            $parts = array_values(array_filter(explode(' ', $company), fn($p) => trim((string) $p) !== ''));
            if (isset($parts[1])) {
                return strtoupper(trim((string) $parts[1]));
            }
        }

        $samaccountname = $ldapUser->getFirstAttribute('samaccountname');
        if (!empty($samaccountname)) {
            return strtoupper((string) $samaccountname);
        }

        return strtoupper($fallback);
    }

    protected function syncWithOracle(User $user): void
    {
        try {
            $oracleData = $this->oracleService->getEmployeeByMatricule((string) $user->matricule);
            if (!$oracleData) {
                return;
            }

            $fonctionOracle = $oracleData['fonction'] ?? null;
            $user->update([
                'oracle_person_id' => $oracleData['person_id'] ?? null,
                'nom' => $oracleData['nom'] ?? $user->nom,
                'prenom' => $oracleData['prenom'] ?? $user->prenom,
                'fonction_oracle' => $fonctionOracle,
                'poste' => $fonctionOracle ?? $user->poste,
                'direction' => $oracleData['direction'] ?? $user->direction,
                'departement' => $oracleData['departement'] ?? $user->departement,
                'service' => $oracleData['service'] ?? $user->service,
                'telephone' => $oracleData['telephone'] ?? $user->telephone,
                'oracle_synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Oracle sync failed', [
                'matricule' => $user->matricule,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleSuccessfulLogin(Request $request, string $matricule): \Illuminate\Http\RedirectResponse
    {
        $user = $this->lastSyncedUser
            ?? User::whereRaw('UPPER(matricule) = ?', [strtoupper($matricule)])->first();

        if (!$user) {
            Log::error('User not found after authentication', ['matricule' => $matricule]);
            return back()->withErrors([
                'matricule' => 'Erreur lors de la recuperation du profil utilisateur.',
            ]);
        }

        if (!$user->is_active) {
            return back()->withErrors(['matricule' => 'Votre compte est desactive. Contactez l\'administrateur.']);
        }

        if ($user->roles->isEmpty()) {
            $user->assignRole('demandeur');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_activity_at' => now()]);

        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'matricule' => $user->matricule,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        // Ne pas garder une URL intended obsolète (ancienne app / mauvais sous-chemin)
        session()->forget('url.intended');

        return $this->redirectByRole($user);
    }

    protected function saveProfilePhoto(string $photoData, string $matricule): ?string
    {
        try {
            $profilPath = public_path('profil');
            if (!is_dir($profilPath)) {
                mkdir($profilPath, 0755, true);
            }

            $extension = 'jpg';
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($photoData);
            if ($mimeType === 'image/png') {
                $extension = 'png';
            } elseif ($mimeType === 'image/gif') {
                $extension = 'gif';
            }

            $filename = strtoupper($matricule) . '.' . $extension;
            $fullPath = $profilPath . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($fullPath, $photoData);

            return 'profil/' . $filename;
        } catch (\Exception $e) {
            Log::warning('Failed to save profile photo', [
                'matricule' => $matricule,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function redirectByRole(User $user): \Illuminate\Http\RedirectResponse
    {
        $userRolesLower = $user->roles->pluck('name')
            ->map(fn ($name) => strtolower((string) $name))
            ->unique()
            ->values();

        // Si l'utilisateur a plusieurs rôles, on l'envoie vers un hub (dashboard)
        // pour éviter qu'un seul rôle "gagne" systématiquement.
        if ($userRolesLower->count() > 1 && Route::has('dashboard')) {
            session()->forget('url.intended');
            return redirect()->route('dashboard');
        }

        // Ordre de priorité : rôle le plus élevé en premier
        $roleRoutes = [
            'admin'       => 'admin.dashboard',
            'dage'        => 'dage.dashboard',
            'sgb'         => 'sgb.dashboard',
            'sad'         => 'sad.dashboard',
            'seg'         => 'seg.dashboard',
            'approbateur' => 'approbateur.dashboard',
            'umt'         => 'umt.dashboard',
            'ubt'         => 'ubt.dashboard',
            'unsp'        => 'unsp.dashboard',
            'umr'         => 'umr.dashboard',
            'utgc'        => 'utgc.dashboard',
            'ual'         => 'ual.dashboard',
            'ucc'         => 'ucc.dashboard',
            'equipe'      => 'equipe.dashboard',
            'demandeur'   => 'demandeur.dashboard',
        ];
        $userRoles = $userRolesLower->toArray();

        foreach ($roleRoutes as $role => $route) {
            if (in_array($role, $userRoles, true) && Route::has($route)) {
                // Ne pas garder une URL intended obsolète (ex: ancienne app/racine)
                session()->forget('url.intended');
                return redirect()->route($route);
            }
        }

        // Fallback
        return redirect()->route('dashboard');
    }
}
