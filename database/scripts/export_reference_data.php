<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'sites' => ['oracle_location_id', 'libelle', 'code', 'adresse', 'ville', 'region', 'is_active'],
    'services' => ['oracle_org_id', 'libelle', 'code', 'centre_responsabilite', 'type', 'is_active'],
    'equipes' => ['nom', 'description'],
    'directions' => ['oracle_org_id', 'libelle', 'code', 'centre_responsabilite', 'type', 'is_active'],
    'departements' => ['oracle_org_id', 'libelle', 'code', 'centre_responsabilite', 'is_active'],
    'delegations' => ['oracle_org_id', 'libelle', 'code', 'centre_responsabilite', 'is_active'],
];

$export = [];

foreach ($tables as $table => $columns) {
    try {
        $rows = DB::table($table)->orderBy('id')->get($columns);
        $export[$table] = $rows->map(fn ($r) => (array) $r)->values()->all();
        echo "{$table}: " . count($export[$table]) . " lignes\n";
    } catch (Throwable $e) {
        echo "{$table}: ERREUR - {$e->getMessage()}\n";
        $export[$table] = [];
    }
}

$path = __DIR__ . '/../seeders/data/reference_data.json';
file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Exporté vers {$path}\n";
