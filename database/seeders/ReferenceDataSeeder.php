<?php

namespace Database\Seeders;

use App\Models\Delegation;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Equipe;
use App\Models\Service;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/reference_data.json');

        if (!File::exists($path)) {
            $this->command?->error("Fichier introuvable : {$path}");
            $this->command?->line('Exécutez : php artisan reference:export (sur Laragon avec vos données)');

            return;
        }

        $data = json_decode(File::get($path), true);

        if (!is_array($data)) {
            $this->command?->error('reference_data.json invalide.');

            return;
        }

        $this->seedSites($data['sites'] ?? []);
        $this->seedServices($data['services'] ?? []);
        $this->seedEquipes($data['equipes'] ?? []);
        $this->seedDirections($data['directions'] ?? []);
        $this->seedDepartements($data['departements'] ?? []);
        $this->seedDelegations($data['delegations'] ?? []);

        $this->command?->info('Données de référence importées depuis reference_data.json.');
    }

    private function seedSites(array $rows): void
    {
        $count = 0;
        foreach ($rows as $row) {
            $key = !empty($row['oracle_location_id'])
                ? ['oracle_location_id' => $row['oracle_location_id']]
                : ['libelle' => $row['libelle']];

            Site::updateOrCreate($key, $this->normalizeRow($row));
            $count++;
        }
        $this->command?->line("  sites: {$count}");
    }

    private function seedServices(array $rows): void
    {
        $count = 0;
        foreach ($rows as $row) {
            $key = !empty($row['oracle_org_id'])
                ? ['oracle_org_id' => $row['oracle_org_id']]
                : ['libelle' => $row['libelle']];

            Service::updateOrCreate($key, $this->normalizeRow($row));
            $count++;
        }
        $this->command?->line("  services: {$count}");
    }

    private function seedEquipes(array $rows): void
    {
        $count = 0;
        foreach ($rows as $row) {
            Equipe::updateOrCreate(
                ['nom' => $row['nom']],
                [
                    'description' => $row['description'] ?? null,
                ]
            );
            $count++;
        }
        $this->command?->line("  equipes: {$count}");
    }

    private function seedDirections(array $rows): void
    {
        $count = 0;
        foreach ($rows as $row) {
            $key = !empty($row['oracle_org_id'])
                ? ['oracle_org_id' => $row['oracle_org_id']]
                : ['libelle' => $row['libelle'], 'type' => $row['type'] ?? 'DIR'];

            Direction::updateOrCreate($key, $this->normalizeRow($row));
            $count++;
        }
        $this->command?->line("  directions: {$count}");
    }

    private function seedDepartements(array $rows): void
    {
        $count = 0;
        foreach ($rows as $row) {
            $key = !empty($row['oracle_org_id'])
                ? ['oracle_org_id' => $row['oracle_org_id']]
                : ['libelle' => $row['libelle']];

            Departement::updateOrCreate($key, $this->normalizeRow($row));
            $count++;
        }
        $this->command?->line("  departements: {$count}");
    }

    private function seedDelegations(array $rows): void
    {
        $count = 0;
        foreach ($rows as $row) {
            $key = !empty($row['oracle_org_id'])
                ? ['oracle_org_id' => $row['oracle_org_id']]
                : ['libelle' => $row['libelle']];

            Delegation::updateOrCreate($key, $this->normalizeRow($row));
            $count++;
        }
        $this->command?->line("  delegations: {$count}");
    }

    private function normalizeRow(array $row): array
    {
        unset($row['id']);

        if (array_key_exists('is_active', $row)) {
            $row['is_active'] = (bool) $row['is_active'];
        }

        return $row;
    }
}
