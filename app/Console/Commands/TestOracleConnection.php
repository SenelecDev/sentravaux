<?php

namespace App\Console\Commands;

use App\Services\OracleHRService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestOracleConnection extends Command
{
    protected $signature = 'oracle:test 
                            {--matricule= : Tester avec un matricule spécifique (ex: C00381)}
                            {--gmao : Tester aussi la connexion GMAO SQL Server}';

    protected $description = 'Teste la connexion Oracle HR et récupère les données d\'un employé';

    public function handle(OracleHRService $oracleService): int
    {
        $this->info('=== Test connexion Oracle HR ===');
        $this->newLine();

        // 1. Test connexion basique
        $this->info('1. Test connexion PDO...');
        try {
            DB::connection('oracle')->getPdo();
            $this->info('   ✓ Connexion Oracle établie.');
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Vérifiez: ORACLE_HOST, ORACLE_PORT, ORACLE_DATABASE, ORACLE_USERNAME, ORACLE_PASSWORD dans .env');
            return self::FAILURE;
        }

        // 2. Test requête simple (SELECT 1)
        $this->info('2. Test requête simple (SELECT 1 FROM DUAL)...');
        try {
            $result = DB::connection('oracle')->select('SELECT 1 as test FROM DUAL');
            $this->info('   ✓ Requête OK. Résultat: ' . json_encode($result));
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur: ' . $e->getMessage());
            return self::FAILURE;
        }

        // 3. Test avec matricule si fourni
        $matricule = $this->option('matricule');
        if ($matricule) {
            $this->newLine();
            $this->info("3. Récupération employé matricule: {$matricule}...");
            try {
                $employee = $oracleService->getEmployeeByMatricule($matricule);
                if ($employee) {
                    $this->info('   ✓ Employé trouvé:');
                    $this->table(
                        ['Champ', 'Valeur'],
                        collect($employee)->map(fn($v, $k) => [$k, $v ?? '-'])->toArray()
                    );
                } else {
                    $this->warn("   Aucun employé trouvé pour le matricule {$matricule}");
                }
            } catch (\Exception $e) {
                $this->error('   ✗ Erreur: ' . $e->getMessage());
                $this->line('   ' . $e->getTraceAsString());
            }
        } else {
            $this->newLine();
            $this->comment('   Astuce: utilisez --matricule=C00381 pour tester la récupération d\'un employé');
        }

        // 4. Test GMAO si demandé (données équipements pour les demandes)
        if ($this->option('gmao')) {
            $this->newLine();
            $this->info('=== Test connexion GMAO SQL Server (équipements) ===');
            $gmaoHost = config('database.connections.sqlsrv_gmao.host');
            $gmaoPort = config('database.connections.sqlsrv_gmao.port');
            $this->line("   Cible: {$gmaoHost}:{$gmaoPort}");
            try {
                DB::connection('sqlsrv_gmao')->getPdo();
                $this->info('   ✓ Connexion GMAO établie.');

                $count = DB::connection('sqlsrv_gmao')
                    ->table('equipment')
                    ->whereIn('ereq_category', ['P-TRANS', 'P-HTB', 'LIGNE-AER', 'LIGNE-SOUT'])
                    ->count();
                $this->info("   ✓ Nombre de lieux d'exécution (postes/lignes): {$count}");
            } catch (\Exception $e) {
                $this->error('   ✗ Erreur GMAO: ' . $e->getMessage());
                $this->warn('   Les équipements afficheront des données démo si GMAO est inaccessible.');
            }
        }

        $this->newLine();
        $this->info('=== Test terminé ===');
        return self::SUCCESS;
    }
}
