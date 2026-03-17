<?php

namespace App\Ldap;

use App\Models\User as DatabaseUser;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Illuminate\Support\Facades\Log;

class LdapAttributeHandler
{
    public function handle(LdapUser $ldap, DatabaseUser $database): void
    {
        // Username
        $database->ldap_username = $ldap->getFirstAttribute('samaccountname');

        // Nom / Prénom
        $displayName = $ldap->getFirstAttribute('displayname') ?? $ldap->getFirstAttribute('name');
        $sn = $ldap->getFirstAttribute('sn');
        $givenName = $ldap->getFirstAttribute('givenname');

        if ($givenName && $sn) {
            $database->prenom = $givenName;
            $database->nom = $sn;
        } elseif ($displayName) {
            $parts = explode(' ', $displayName, 2);
            $database->prenom = $parts[0] ?? '';
            $database->nom = $parts[1] ?? '';
        }

        $database->name = trim(($database->prenom ?? '') . ' ' . ($database->nom ?? ''));

        // Poste / Email / Service / Téléphone
        $database->poste = $ldap->getFirstAttribute('title');
        
        $email = $ldap->getFirstAttribute('mail');
        if ($email) {
            $database->email = $email;
        } elseif (!$database->email) {
            $sam = $ldap->getFirstAttribute('samaccountname');
            $database->email = $sam ? strtolower($sam) . '@senelec.sn' : null;
        }

        $database->service = $ldap->getFirstAttribute('department');
        $database->telephone = $ldap->getFirstAttribute('mobile') ?: $ldap->getFirstAttribute('telephonenumber');

        // Organisation & Matricule
        $company = $ldap->getFirstAttribute('company');
        if ($company) {
            $database->organisation = $company;

            // Extraction du matricule depuis company (format "SENELEC XXXXX")
            if (preg_match('/SENELEC\s+(\S+)/i', $company, $matches)) {
                $database->matricule = strtoupper($matches[1]);
            }
        }

        if (!$database->matricule) {
            $sam = $ldap->getFirstAttribute('samaccountname');
            $database->matricule = $sam ? strtoupper($sam) : 'AUTO-' . strtoupper(uniqid());
        }

        // Photo de profil
        $thumbnailPhoto = $ldap->getFirstAttribute('thumbnailPhoto');
        if ($thumbnailPhoto) {
            try {
                $filename = 'profil/' . $database->matricule . '.jpg';
                $publicPath = public_path($filename);
                $dir = dirname($publicPath);
                
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                file_put_contents($publicPath, $thumbnailPhoto);
                $database->photo = $filename;
            } catch (\Exception $e) {
                Log::warning('Failed to save LDAP photo: ' . $e->getMessage());
                $database->photo = 'data:image/jpeg;base64,' . base64_encode($thumbnailPhoto);
            }
        }

        // Mot de passe par défaut si nouveau
        if (!$database->exists) {
            $database->password = bcrypt('password');
            $database->is_active = true;
        }

        $database->last_sync_at = now();
    }
}
