<?php

namespace App\Console\Commands;

use App\Models\Delegation;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Service;
use App\Models\Site;
use App\Services\OracleHRService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOracleReferences extends Command
{
    protected $signature = 'sync:oracle-references
                            {--services : Importer uniquement les services}
                            {--sites : Importer uniquement les sites/localisations}
                            {--directions : Importer uniquement les directions (DG, DIRP, DIR)}
                            {--departements : Importer uniquement les départements}
                            {--delegations : Importer uniquement les délégations}
                            {--all : Importer toutes les données de référence (par défaut)}
                            {--dry-run : Afficher les résultats sans modifier la base}';

    protected $description = 'Importer les services, sites, directions, départements et délégations depuis Oracle HR';

    protected OracleHRService $oracleService;

    protected array $stats = [
        'services' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
        'sites' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
        'directions' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
        'departements' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
        'delegations' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
    ];

    public function __construct(OracleHRService $oracleService)
    {
        parent::__construct();
        $this->oracleService = $oracleService;
    }

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║   SENTRAVAUX — Import données de référence Oracle HR    ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Vérifier la connexion Oracle
        $this->info('Vérification de la connexion Oracle...');
        if (!$this->oracleService->isAvailable()) {
            $this->error('❌ Impossible de se connecter à Oracle. Vérifiez les paramètres de connexion.');
            return Command::FAILURE;
        }
        $this->info('✅ Connexion Oracle établie.');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('⚡ Mode simulation — aucune modification ne sera effectuée.');
            $this->newLine();
        }

        // Déterminer quoi importer
        $specific = $this->option('services') || $this->option('sites') || $this->option('directions')
                    || $this->option('departements') || $this->option('delegations');
        $importAll = $this->option('all') || !$specific;

        // Import séquentiel
        if ($importAll || $this->option('directions')) {
            $this->importDirections($dryRun);
        }
        if ($importAll || $this->option('departements')) {
            $this->importDepartements($dryRun);
        }
        if ($importAll || $this->option('delegations')) {
            $this->importDelegations($dryRun);
        }
        if ($importAll || $this->option('services')) {
            $this->importServices($dryRun);
        }
        if ($importAll || $this->option('sites')) {
            $this->importSites($dryRun);
        }

        // Résumé
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║                      RÉSUMÉ                             ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');

        $headers = ['Table', 'Créés', 'Mis à jour', 'Ignorés', 'Total Oracle'];
        $rows = [];

        foreach ($this->stats as $table => $stat) {
            if ($stat['created'] + $stat['updated'] + $stat['skipped'] > 0) {
                $rows[] = [
                    ucfirst($table),
                    $stat['created'],
                    $stat['updated'],
                    $stat['skipped'],
                    $stat['created'] + $stat['updated'] + $stat['skipped'],
                ];
            }
        }

        if (!empty($rows)) {
            $this->table($headers, $rows);
        }

        $totalCreated = array_sum(array_column($this->stats, 'created'));
        $totalUpdated = array_sum(array_column($this->stats, 'updated'));
        $this->newLine();
        $this->info("✅ Import terminé : {$totalCreated} créés, {$totalUpdated} mis à jour.");

        if ($dryRun) {
            $this->warn('⚡ Mode simulation — rien n\'a été modifié en base.');
        }

        Log::info("SyncOracleReferences terminé", $this->stats);

        return Command::SUCCESS;
    }

    /**
     * Importer les directions (DG + DIRP + DIR)
     */
    protected function importDirections(bool $dryRun): void
    {
        $this->info('📁 Import des directions...');

        $types = ['DG', 'DIRP', 'DIR'];
        $allDirections = [];

        foreach ($types as $type) {
            $this->comment("  ↳ Type {$type}...");
            $orgs = $this->oracleService->getOrganizationsByType($type);
            $allDirections = array_merge($allDirections, $orgs);
        }

        $bar = $this->output->createProgressBar(count($allDirections));
        $bar->start();

        foreach ($allDirections as $org) {
            if ($dryRun) {
                $existing = Direction::where('oracle_org_id', $org['oracle_org_id'])->exists();
                $this->stats['directions'][$existing ? 'updated' : 'created']++;
            } else {
                $direction = Direction::updateOrCreate(
                    ['oracle_org_id' => $org['oracle_org_id']],
                    [
                        'libelle' => $org['libelle'],
                        'centre_responsabilite' => $org['centre_responsabilite'],
                        'type' => $org['type'],
                        'is_active' => true,
                    ]
                );
                $this->stats['directions'][$direction->wasRecentlyCreated ? 'created' : 'updated']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✅ Directions : {$this->stats['directions']['created']} créées, {$this->stats['directions']['updated']} mises à jour");
        $this->newLine();
    }

    /**
     * Importer les départements (DEP)
     */
    protected function importDepartements(bool $dryRun): void
    {
        $this->info('📁 Import des départements...');

        $orgs = $this->oracleService->getOrganizationsByType('DEP');
        $bar = $this->output->createProgressBar(count($orgs));
        $bar->start();

        foreach ($orgs as $org) {
            if ($dryRun) {
                $existing = Departement::where('oracle_org_id', $org['oracle_org_id'])->exists();
                $this->stats['departements'][$existing ? 'updated' : 'created']++;
            } else {
                $dep = Departement::updateOrCreate(
                    ['oracle_org_id' => $org['oracle_org_id']],
                    [
                        'libelle' => $org['libelle'],
                        'centre_responsabilite' => $org['centre_responsabilite'],
                        'is_active' => true,
                    ]
                );
                $this->stats['departements'][$dep->wasRecentlyCreated ? 'created' : 'updated']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✅ Départements : {$this->stats['departements']['created']} créés, {$this->stats['departements']['updated']} mis à jour");
        $this->newLine();
    }

    /**
     * Importer les délégations (DEL)
     */
    protected function importDelegations(bool $dryRun): void
    {
        $this->info('📁 Import des délégations...');

        $orgs = $this->oracleService->getOrganizationsByType('DEL');
        $bar = $this->output->createProgressBar(count($orgs));
        $bar->start();

        foreach ($orgs as $org) {
            if ($dryRun) {
                $existing = Delegation::where('oracle_org_id', $org['oracle_org_id'])->exists();
                $this->stats['delegations'][$existing ? 'updated' : 'created']++;
            } else {
                $del = Delegation::updateOrCreate(
                    ['oracle_org_id' => $org['oracle_org_id']],
                    [
                        'libelle' => $org['libelle'],
                        'centre_responsabilite' => $org['centre_responsabilite'],
                        'is_active' => true,
                    ]
                );
                $this->stats['delegations'][$del->wasRecentlyCreated ? 'created' : 'updated']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✅ Délégations : {$this->stats['delegations']['created']} créées, {$this->stats['delegations']['updated']} mises à jour");
        $this->newLine();
    }

    /**
     * Importer les services (SER)
     */
    protected function importServices(bool $dryRun): void
    {
        $this->info('📁 Import des services...');

        $orgs = $this->oracleService->getOrganizationsByType('SER');
        $bar = $this->output->createProgressBar(count($orgs));
        $bar->start();

        foreach ($orgs as $org) {
            if ($dryRun) {
                $existing = Service::where('oracle_org_id', $org['oracle_org_id'])->exists();
                $this->stats['services'][$existing ? 'updated' : 'created']++;
            } else {
                $service = Service::updateOrCreate(
                    ['oracle_org_id' => $org['oracle_org_id']],
                    [
                        'libelle' => $org['libelle'],
                        'centre_responsabilite' => $org['centre_responsabilite'],
                        'type' => 'SER',
                        'is_active' => true,
                    ]
                );
                $this->stats['services'][$service->wasRecentlyCreated ? 'created' : 'updated']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✅ Services : {$this->stats['services']['created']} créés, {$this->stats['services']['updated']} mis à jour");
        $this->newLine();
    }

    /**
     * Importer les sites/localisations
     */
    protected function importSites(bool $dryRun): void
    {
        $this->info('📁 Import des sites/localisations...');

        $locations = $this->oracleService->getLocations();
        $bar = $this->output->createProgressBar(count($locations));
        $bar->start();

        foreach ($locations as $loc) {
            if ($dryRun) {
                $existing = Site::where('oracle_location_id', $loc['oracle_location_id'])->exists();
                $this->stats['sites'][$existing ? 'updated' : 'created']++;
            } else {
                $site = Site::updateOrCreate(
                    ['oracle_location_id' => $loc['oracle_location_id']],
                    [
                        'libelle' => $loc['libelle'],
                        'code' => $loc['code'],
                        'adresse' => $loc['adresse'],
                        'ville' => $loc['ville'],
                        'region' => $loc['region'],
                        'is_active' => true,
                    ]
                );
                $this->stats['sites'][$site->wasRecentlyCreated ? 'created' : 'updated']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✅ Sites : {$this->stats['sites']['created']} créés, {$this->stats['sites']['updated']} mis à jour");
        $this->newLine();
    }
}
