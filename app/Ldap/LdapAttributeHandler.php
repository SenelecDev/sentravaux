<?php

namespace App\Ldap;

use App\Models\User as DatabaseUser;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Illuminate\Support\Facades\Log;

class LdapAttributeHandler
{
    public function handle(LdapUser $ldap, DatabaseUser $database): void
    {
        $sam = (string) ($ldap->getFirstAttribute('samaccountname') ?? '');
        $sn = (string) ($ldap->getFirstAttribute('sn') ?? '');
        $givenName = (string) ($ldap->getFirstAttribute('givenname') ?? '');
        $displayName = (string) ($ldap->getFirstAttribute('displayname') ?? $ldap->getFirstAttribute('name') ?? '');
        $company = (string) ($ldap->getFirstAttribute('company') ?? '');
        $email = $ldap->getFirstAttribute('mail');

        $database->ldap_username = $sam !== '' ? $sam : null;
        $database->ldap_guid = $ldap->getConvertedGuid();

        // Nom / prenom
        $database->nom = $sn !== '' ? $sn : ($database->nom ?? '');
        $database->prenom = $givenName !== '' ? $givenName : ($database->prenom ?? '');
        if (($database->prenom === '' || $database->nom === '') && $displayName !== '') {
            $parts = explode(' ', $displayName, 2);
            $database->prenom = $database->prenom !== '' ? $database->prenom : (string) ($parts[0] ?? '');
            $database->nom = $database->nom !== '' ? $database->nom : (string) ($parts[1] ?? '');
        }

        $fullName = trim(($database->prenom ?? '') . ' ' . ($database->nom ?? ''));
        $database->name = $fullName !== '' ? $fullName : ($displayName !== '' ? $displayName : ($sam !== '' ? $sam : 'Utilisateur'));

        // Donnees pro
        $database->poste = $ldap->getFirstAttribute('title') ?? $database->poste;
        $database->service = $ldap->getFirstAttribute('department') ?? $database->service;
        $database->telephone = $ldap->getFirstAttribute('mobile')
            ?? $ldap->getFirstAttribute('telephonenumber')
            ?? $database->telephone;

        if (!empty($email)) {
            $database->email = (string) $email;
        } elseif (empty($database->email) && $sam !== '') {
            $database->email = strtolower($sam) . '@senelec.sn';
        }

        // Organisation / entreprise
        if ($company !== '') {
            $database->organisation = $company;
            $split = preg_split('/\s+/', trim($company)) ?: [];
            $database->entreprise = isset($split[0]) ? strtoupper((string) $split[0]) : ($database->entreprise ?? 'SENELEC');
        } elseif (empty($database->entreprise)) {
            $database->entreprise = 'SENELEC';
        }

        // Matricule: employeenumber > company token > samaccountname > auto
        $database->matricule = $this->extractMatriculeFromLdap($ldap, $sam);

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

    protected function extractMatriculeFromLdap(LdapUser $ldap, string $sam): string
    {
        $employeeNumber = $ldap->getFirstAttribute('employeenumber');
        if (!empty($employeeNumber)) {
            return strtoupper((string) $employeeNumber);
        }

        $company = (string) ($ldap->getFirstAttribute('company') ?? '');
        if ($company !== '') {
            $parts = array_values(array_filter(preg_split('/\s+/', trim($company)) ?: []));
            if (isset($parts[1]) && (string) $parts[1] !== '') {
                return strtoupper((string) $parts[1]);
            }
        }

        if ($sam !== '') {
            return strtoupper($sam);
        }

        return 'AUTO_' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }
}
