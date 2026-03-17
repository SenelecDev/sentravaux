<?php

namespace Database\Seeders;

use App\Models\Equipe;
use Illuminate\Database\Seeder;

class EquipeFromConfigSeeder extends Seeder
{
    /**
     * Seed the equipes table from config/services_structure.php.
     *
     * Les équipes correspondent aux valeurs après chaque nature
     * (GMMB, GTE, GNS, GP Pool, Equipe Froid, etc.).
     * On vide la table puis on insère une ligne par libellé unique.
     */
    public function run(): void
    {
        // ATTENTION : ceci supprime toutes les équipes existantes
        // On supprime d'abord les liens dans la table pivot pour éviter les erreurs de FK,
        // puis on vide la table equipes.
        \DB::table('demande_equipe')->delete();
        \DB::table('equipes')->delete();

        $structure = config('services_structure.services_structure');

        $labels = [];

        foreach ($structure as $service) {
            if (!isset($service['unites']) || !is_array($service['unites'])) {
                continue;
            }

            foreach ($service['unites'] as $unite) {
                if (!isset($unite['natures']) || !is_array($unite['natures'])) {
                    continue;
                }

                foreach ($unite['natures'] as $nature => $equipeLabel) {
                    // On ignore les valeurs vides et "Autres"
                    if (!$equipeLabel) {
                        continue;
                    }
                    if (mb_strtolower($equipeLabel) === 'autres') {
                        continue;
                    }

                    $labels[] = $equipeLabel;
                }
            }
        }

        foreach (array_unique($labels) as $label) {
            Equipe::create([
                'nom' => $label,
                'description' => null,
            ]);
        }
    }
}

