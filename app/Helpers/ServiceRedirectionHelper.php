<?php

namespace App\Helpers;

class ServiceRedirectionHelper
{
    /**
     * Obtenir le service de destination selon la nature des travaux
     */
    public static function getServiceFromNature($nature, $uniteCode = null)
    {
        // Si on connaît déjà l'unité (UAG, UPNS, UGBT, UTGC, UMR, ...)
        // on s'en sert en priorité pour déterminer le service (SA ou SEG),
        // y compris pour "Autres demandes".
        if ($uniteCode) {
            $uniteInfo = self::getUniteFromNature($nature, $uniteCode);
            if ($uniteInfo) {
                $serviceName = $uniteInfo['service'];
                return [
                    'service_name' => $serviceName,
                    'role_name' => $serviceName === 'SA' ? 'sad' : 'seg'
                ];
            }
        }

        $mapping = config('services_structure.nature_to_service_mapping');
        $serviceName = $mapping[$nature] ?? null;

        if (!$serviceName) {
            return null;
        }

        return [
            'service_name' => $serviceName,
            'role_name' => $serviceName === 'SA' ? 'sad' : 'seg'
        ];
    }

    /**
     * Obtenir l'équipe/groupe assigné selon la nature des travaux
     */
    public static function getEquipeFromNature($nature)
    {
        $structure = config('services_structure.services_structure');

        foreach ($structure['SA']['unites'] as $unite) {
            if (isset($unite['natures'][$nature])) {
                return $unite['natures'][$nature];
            }
        }

        foreach ($structure['SEG']['unites'] as $unite) {
            if (isset($unite['natures'][$nature])) {
                return $unite['natures'][$nature];
            }
        }

        return null;
    }

    /**
     * Obtenir le rôle de traitement selon la nature des travaux
     */
    public static function getRoleFromNature($nature, $uniteCode = null)
    {
        $serviceInfo = self::getServiceFromNature($nature, $uniteCode);
        return $serviceInfo ? $serviceInfo['role_name'] : null;
    }

    /**
     * Obtenir l'unité responsable selon la nature des travaux
     */
    public static function getUniteFromNature($nature, $uniteCode = null)
    {
        $structure = config('services_structure.services_structure');

        // Si un code d'unité est fourni (UAG, UPNS, UGBT, UTGC, UMR, ...),
        // on cherche d'abord dans cette unité précise, pour éviter
        // les ambiguïtés sur "Autres demandes" qui existe côté SA et SEG.
        if ($uniteCode) {
            if (
                isset($structure['SA']['unites'][$uniteCode]) &&
                isset($structure['SA']['unites'][$uniteCode]['natures'][$nature])
            ) {
                return [
                    'code' => $uniteCode,
                    'name' => $structure['SA']['unites'][$uniteCode]['name'],
                    'service' => 'SA'
                ];
            }

            if (
                isset($structure['SEG']['unites'][$uniteCode]) &&
                isset($structure['SEG']['unites'][$uniteCode]['natures'][$nature])
            ) {
                return [
                    'code' => $uniteCode,
                    'name' => $structure['SEG']['unites'][$uniteCode]['name'],
                    'service' => 'SEG'
                ];
            }
        }

        foreach ($structure['SA']['unites'] as $code => $unite) {
            if (isset($unite['natures'][$nature])) {
                return ['code' => $code, 'name' => $unite['name'], 'service' => 'SA'];
            }
        }

        foreach ($structure['SEG']['unites'] as $code => $unite) {
            if (isset($unite['natures'][$nature])) {
                return ['code' => $code, 'name' => $unite['name'], 'service' => 'SEG'];
            }
        }

        return null;
    }

    /**
     * Obtenir toutes les natures disponibles groupées par service
     */
    public static function getAllNaturesGrouped()
    {
        $structure = config('services_structure.services_structure');
        $groupedNatures = [];

        foreach ($structure as $serviceCode => $service) {
            $groupedNatures[$serviceCode] = [
                'name' => $service['name'],
                'unites' => []
            ];
            foreach ($service['unites'] as $uniteCode => $unite) {
                $groupedNatures[$serviceCode]['unites'][$uniteCode] = [
                    'name' => $unite['name'],
                    'natures' => array_keys($unite['natures'])
                ];
            }
        }

        return $groupedNatures;
    }

    /**
     * Obtenir la liste structurée des natures pour les formulaires
     */
    public static function getStructuredNatures()
    {
        $structure = config('services_structure.services_structure');
        $result = [];

        foreach ($structure['SA']['unites'] as $uniteCode => $unite) {
            $result['SA - ' . $unite['name']] = array_keys($unite['natures']);
        }
        foreach ($structure['SEG']['unites'] as $uniteCode => $unite) {
            $result['SEG - ' . $unite['name']] = array_keys($unite['natures']);
        }

        return $result;
    }

    /**
     * Mapping label optgroup → code unité (ex: "SA - Unité Affaire Générale" → "UAG")
     */
    public static function getOptgroupToUniteCodeMap()
    {
        $structure = config('services_structure.services_structure');
        $map = [];

        foreach ($structure as $serviceCode => $service) {
            foreach ($service['unites'] as $uniteCode => $unite) {
                $map[$serviceCode . ' - ' . $unite['name']] = $uniteCode;
            }
        }

        return $map;
    }

    /**
     * Vérifier si un utilisateur a le rôle correspondant à une unité
     */
    public static function userHasUnitRole($user, $unitCode)
    {
        $roleMapping = [
            'UAG' => 'umt',
            'UGBT' => 'ubt',
            'UPNS' => 'unsp',
            'UTGC' => 'utgc',
            'UMR' => 'umr'
        ];

        $requiredRole = $roleMapping[$unitCode] ?? null;
        return $requiredRole ? $user->hasRole($requiredRole) : false;
    }

    /**
     * Obtenir le rôle correspondant à une unité
     */
    public static function getRoleFromUnite($uniteCode)
    {
        $roleMapping = [
            'UAG' => 'umt',
            'UGBT' => 'ubt',
            'UPNS' => 'unsp',
            'UTGC' => 'utgc',
            'UMR' => 'umr'
        ];

        return $roleMapping[$uniteCode] ?? null;
    }

    /**
     * Obtenir le rôle du service responsable selon la nature
     */
    public static function getServiceRoleForNature($nature, $uniteCode = null)
    {
        $uniteInfo = self::getUniteFromNature($nature, $uniteCode);
        if (!$uniteInfo) {
            return null;
        }

        $serviceRoleMapping = ['SA' => 'sad', 'SEG' => 'seg'];
        return $serviceRoleMapping[$uniteInfo['service']] ?? null;
    }

    /**
     * Obtenir un utilisateur responsable au niveau service pour une nature donnée
     */
    public static function getServiceManagerForNature($nature, $uniteCode = null)
    {
        $serviceRole = self::getServiceRoleForNature($nature, $uniteCode);
        if (!$serviceRole) {
            return null;
        }

        return \App\Models\User::whereHas('roles', function ($query) use ($serviceRole) {
            $query->where('name', $serviceRole);
        })->first();
    }

    /**
     * Obtenir un utilisateur d'unité pour affectation finale
     */
    public static function getUniteUserForNature($nature, $uniteCode = null)
    {
        $uniteInfo = self::getUniteFromNature($nature, $uniteCode);
        if (!$uniteInfo) {
            return null;
        }

        $role = self::getRoleFromUnite($uniteInfo['code']);
        if (!$role) {
            return null;
        }

        return \App\Models\User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->first();
    }

    /**
     * Obtenir les natures accessibles selon les rôles de l'utilisateur
     */
    public static function getAccessibleNatures($user)
    {
        $structure = config('services_structure.services_structure');
        $result = [];

        foreach ($structure['SA']['unites'] as $uniteCode => $unite) {
            if (self::userHasUnitRole($user, $uniteCode)) {
                $result['SA - ' . $unite['name']] = array_keys($unite['natures']);
            }
        }
        foreach ($structure['SEG']['unites'] as $uniteCode => $unite) {
            if (self::userHasUnitRole($user, $uniteCode)) {
                $result['SEG - ' . $unite['name']] = array_keys($unite['natures']);
            }
        }

        return $result;
    }

    /**
     * Obtenir les informations de redirection pour affichage
     */
    public static function getRedirectionInfo($demande)
    {
        $unite = null;
        $personnel = null;

        if ($demande->umt) {
            $unite = 'UMT (Unité de Maintenance Technique)';
            $personnel = $demande->umt->name;
        } elseif ($demande->ubt) {
            $unite = 'UBT (Unité de Bâtiment et Tertiaire)';
            $personnel = $demande->ubt->name;
        } elseif ($demande->unsp) {
            $unite = 'UNSP (Unité de Nettoyage et Soins Portés)';
            $personnel = $demande->unsp->name;
        } elseif ($demande->utgc) {
            $unite = 'UTGC (Unité Technique de Gestion Communale)';
            $personnel = $demande->utgc->name;
        } elseif ($demande->umr) {
            $unite = 'UMR (Unité de Maintenance et Réparation)';
            $personnel = $demande->umr->name;
        } elseif ($demande->sad) {
            $unite = 'SAD (Service Administratif)';
            $personnel = $demande->sad->name;
        } elseif ($demande->seg) {
            $unite = 'SEG (Service d\'Entretien Général)';
            $personnel = $demande->seg->name;
        }

        return $unite ? ['unite' => $unite, 'personnel' => $personnel] : null;
    }
}
