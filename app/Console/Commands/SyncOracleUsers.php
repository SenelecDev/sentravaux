<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OracleHRService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use LdapRecord\Container;
use LdapRecord\Models\Entry;

class SyncOracleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-oracle 
                            {--matricule= : Synchroniser un utilisateur spécifique par matricule}
                            {--all : Synchroniser tous les utilisateurs existants}
                            {--import : Importer de nouveaux utilisateurs depuis Oracle}
                            {--import-all : Importer TOUS les employés depuis Oracle (avec complétion LDAP)}
                            {--ldap : Synchroniser les données LDAP (username, telephone, photo, entreprise)}
                            {--photos : Synchroniser uniquement les photos depuis LDAP (rapide)}
                            {--limit=5000 : Nombre maximum d\'utilisateurs à importer}
                            {--batch=5000 : Nombre d\'employés à traiter par lot}
                            {--dry-run : Afficher les actions sans les exécuter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les utilisateurs depuis la base Oracle HR vers la base locale';

    protected OracleHRService $oracleService;

    protected string $syncLogFile = 'sync_oracle.log';
    protected string $syncStatusFile = 'sync_oracle_status.json';

    public function __construct(OracleHRService $oracleService)
    {
        parent::__construct();
        $this->oracleService = $oracleService;
    }

    /**
     * Chemin du fichier de log
     */
    protected function getLogFilePath(): string
    {
        return storage_path('logs/' . $this->syncLogFile);
    }

    /**
     * Chemin du fichier de statut
     */
    protected function getStatusFilePath(): string
    {
        return storage_path('logs/' . $this->syncStatusFile);
    }

    /**
     * Log un message dans le fichier pour l'affichage en temps réel
     */
    protected function logProgress(string $message, string $type = 'info'): void
    {
        $logLine = json_encode([
            'time' => now()->format('H:i:s'),
            'type' => $type,
            'message' => $message,
        ]) . "\n";
        
        file_put_contents($this->getLogFilePath(), $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Initialiser les logs de synchronisation
     */
    protected function initSyncLog(string $operation): void
    {
        // Vider le fichier de log
        file_put_contents($this->getLogFilePath(), '');
        
        // Écrire le premier message
        $this->logProgress("🚀 Début: {$operation}", 'start');
        
        // Écrire le statut
        $status = [
            'running' => true,
            'operation' => $operation,
            'started_at' => now()->toIso8601String(),
            'progress' => 0,
            'total' => 0,
        ];
        file_put_contents($this->getStatusFilePath(), json_encode($status), LOCK_EX);
    }

    /**
     * Mettre à jour la progression
     */
    protected function updateProgress(int $current, int $total): void
    {
        $statusFile = $this->getStatusFilePath();
        $status = [];
        
        if (file_exists($statusFile)) {
            $content = file_get_contents($statusFile);
            $status = json_decode($content, true) ?? [];
        }
        
        $status['progress'] = $current;
        $status['total'] = $total;
        
        file_put_contents($statusFile, json_encode($status), LOCK_EX);
    }

    /**
     * Terminer les logs de synchronisation
     */
    protected function endSyncLog(string $summary): void
    {
        $this->logProgress("✅ {$summary}", 'success');
        
        $statusFile = $this->getStatusFilePath();
        $status = [];
        
        if (file_exists($statusFile)) {
            $content = file_get_contents($statusFile);
            $status = json_decode($content, true) ?? [];
        }
        
        $status['running'] = false;
        $status['finished_at'] = now()->toIso8601String();
        
        file_put_contents($statusFile, json_encode($status), LOCK_EX);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Synchronisation des utilisateurs Oracle ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('Mode simulation activé - aucune modification ne sera effectuée');
            $this->newLine();
        }

        // Synchroniser un utilisateur spécifique
        if ($matricule = $this->option('matricule')) {
            return $this->syncSingleUser($matricule, $dryRun);
        }

        // Synchroniser uniquement les données LDAP
        if ($this->option('ldap')) {
            return $this->syncLdapDataForAllUsers($dryRun);
        }

        // Synchroniser uniquement les photos LDAP (rapide)
        if ($this->option('photos')) {
            return $this->syncPhotosOnly($dryRun);
        }

        // Synchroniser tous les utilisateurs existants
        if ($this->option('all')) {
            return $this->syncAllExistingUsers($dryRun);
        }

        // Importer de nouveaux utilisateurs depuis Oracle
        if ($this->option('import')) {
            return $this->importNewUsers($dryRun);
        }

        // Importer TOUS les employés depuis Oracle avec complétion LDAP
        if ($this->option('import-all')) {
            return $this->importAllFromOracleWithLdap($dryRun);
        }

        // Par défaut, synchroniser les utilisateurs existants avec un matricule
        return $this->syncAllExistingUsers($dryRun);
    }

    /**
     * Synchroniser les données LDAP pour tous les utilisateurs
     */
    protected function syncLdapDataForAllUsers(bool $dryRun = false): int
    {
        $this->info('Synchronisation des données LDAP pour tous les utilisateurs...');
        $this->newLine();

        $users = User::whereNotNull('matricule')
            ->where(function($q) {
                $q->whereNull('ldap_username')
                  ->orWhereNull('telephone')
                  ->orWhereNull('entreprise');
            })
            ->get();

        $this->info("Nombre d'utilisateurs à synchroniser: " . $users->count());
        $this->newLine();

        if ($users->isEmpty()) {
            $this->info('Tous les utilisateurs ont déjà leurs données LDAP complètes.');
            return Command::SUCCESS;
        }

        $synced = 0;
        $notFound = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                $ldapData = $this->findUserInLdap($user->matricule, $user->nom ?? '', $user->prenom ?? '');
                
                if ($ldapData) {
                    $updateData = [];
                    
                    if (empty($user->ldap_username) && !empty($ldapData['ldap_username'])) {
                        $updateData['ldap_username'] = $ldapData['ldap_username'];
                    }
                    if (empty($user->telephone) && !empty($ldapData['telephone'])) {
                        $updateData['telephone'] = $ldapData['telephone'];
                    }
                    if (empty($user->photo) && !empty($ldapData['photo'])) {
                        $updateData['photo'] = $ldapData['photo'];
                    }
                    if (empty($user->entreprise) && !empty($ldapData['entreprise'])) {
                        $updateData['entreprise'] = $ldapData['entreprise'];
                    }
                    // Aussi mettre à jour l'email si c'est un fake
                    if (str_ends_with($user->email, $user->matricule . '@senelec.sn') && !empty($ldapData['email'])) {
                        $updateData['email'] = $ldapData['email'];
                    }
                    
                    if (!empty($updateData) && !$dryRun) {
                        $user->update($updateData);
                        $synced++;
                    } elseif (!empty($updateData)) {
                        $synced++;
                    }
                } else {
                    $notFound++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::warning("LDAP sync error for {$user->matricule}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=== Résumé de la synchronisation LDAP ===");
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Synchronisés', $synced],
                ['Non trouvés dans LDAP', $notFound],
                ['Erreurs', $errors],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Synchroniser uniquement les photos depuis LDAP (rapide)
     */
    protected function syncPhotosOnly(bool $dryRun = false): int
    {
        $this->initSyncLog('Synchronisation Photos LDAP');
        $this->info('Synchronisation des photos LDAP uniquement...');
        $this->logProgress('Synchronisation des photos LDAP uniquement...');
        $this->newLine();

        // Utilisateurs sans photo et avec matricule
        $users = User::whereNotNull('matricule')
            ->where('matricule', '!=', '')
            ->where(function($q) {
                $q->whereNull('photo')
                  ->orWhere('photo', '');
            })
            ->get();

        $this->info("Utilisateurs sans photo: " . $users->count());
        $this->logProgress("Utilisateurs sans photo: " . $users->count());
        $this->newLine();

        if ($users->isEmpty()) {
            $this->info('Tous les utilisateurs ont déjà une photo.');
            return Command::SUCCESS;
        }

        $synced = 0;
        $notFound = 0;
        $errors = 0;

        // Créer le dossier profil s'il n'existe pas
        $profileDir = public_path('profil');
        if (!file_exists($profileDir)) {
            mkdir($profileDir, 0755, true);
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        try {
            $connection = Container::getConnection('default');
            $baseDn = $connection->getConfiguration()->get('base_dn');

            foreach ($users as $user) {
                try {
                    // Recherche directe de la photo seulement
                    $query = $connection->query();
                    $results = $query->setDn($baseDn)
                        ->rawFilter('(&(objectClass=person)(company=*' . ldap_escape($user->matricule, '', LDAP_ESCAPE_FILTER) . '))')
                        ->limit(1)
                        ->get();
                    
                    if (!empty($results)) {
                        $entry = $results[0];
                        $photoData = null;
                        
                        if (isset($entry['thumbnailphoto'])) {
                            $photoData = is_array($entry['thumbnailphoto']) ? $entry['thumbnailphoto'][0] : $entry['thumbnailphoto'];
                        } elseif (isset($entry['jpegphoto'])) {
                            $photoData = is_array($entry['jpegphoto']) ? $entry['jpegphoto'][0] : $entry['jpegphoto'];
                        }
                        
                        if ($photoData && !$dryRun) {
                            $imagePath = 'profil/' . $user->matricule . '.jpg';
                            file_put_contents(public_path($imagePath), $photoData);
                            $user->update(['photo' => $imagePath]);
                            $synced++;
                        } elseif ($photoData) {
                            $synced++;
                        } else {
                            $notFound++;
                        }
                    } else {
                        $notFound++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                }

                $bar->advance();
            }
        } catch (\Exception $e) {
            $this->error("Erreur LDAP: " . $e->getMessage());
            return Command::FAILURE;
        }

        $bar->finish();
        $this->newLine(2);

        $summary = "Photos: {$synced} synchronisées, {$notFound} non trouvées, {$errors} erreurs";
        $this->info("=== Résumé de la synchronisation des photos ===");
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Photos synchronisées', $synced],
                ['Sans photo dans LDAP', $notFound],
                ['Erreurs', $errors],
            ]
        );
        
        $this->endSyncLog($summary);

        return Command::SUCCESS;
    }

    /**
     * Synchroniser un seul utilisateur par matricule
     */
    protected function syncSingleUser(string $matricule, bool $dryRun = false): int
    {
        $this->info("Recherche de l'utilisateur avec matricule: {$matricule}");

        try {
            $oracleData = $this->oracleService->getEmployeeByMatricule($matricule);

            if (!$oracleData) {
                $this->error("Aucun employé trouvé dans Oracle avec le matricule: {$matricule}");
                return Command::FAILURE;
            }

            $this->displayOracleData($oracleData);

            $user = User::where('matricule', $matricule)->first();

            if (!$user) {
                $this->warn("L'utilisateur n'existe pas dans la base locale.");
                
                if ($this->confirm('Voulez-vous créer cet utilisateur?', true)) {
                    if (!$dryRun) {
                        $user = $this->createUserFromOracle($oracleData);
                        $this->info("✓ Utilisateur créé: {$user->full_name}");
                    } else {
                        $this->info("[DRY-RUN] L'utilisateur serait créé");
                    }
                }
            } else {
                $this->info("Utilisateur existant: {$user->full_name}");
                
                if (!$dryRun) {
                    $this->updateUserFromOracle($user, $oracleData);
                    $this->info("✓ Utilisateur mis à jour");
                } else {
                    $this->info("[DRY-RUN] L'utilisateur serait mis à jour");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            Log::error("SyncOracleUsers Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Synchroniser tous les utilisateurs existants qui ont un matricule
     */
    protected function syncAllExistingUsers(bool $dryRun = false): int
    {
        $users = User::whereNotNull('matricule')
            ->where('matricule', '!=', '')
            ->get();

        $this->info("Nombre d'utilisateurs à synchroniser: {$users->count()}");
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($users as $user) {
            try {
                $oracleData = $this->oracleService->getEmployeeByMatricule($user->matricule);

                if ($oracleData) {
                    if (!$dryRun) {
                        $this->updateUserFromOracle($user, $oracleData);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::warning("Sync error for user {$user->matricule}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=== Résumé ===");
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Mis à jour', $updated],
                ['Non trouvés dans Oracle', $skipped],
                ['Erreurs', $errors],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Importer de nouveaux utilisateurs depuis Oracle
     */
    protected function importNewUsers(bool $dryRun = false): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Importation de nouveaux utilisateurs depuis Oracle (limite: {$limit})");
        $this->newLine();

        try {
            // Récupérer les employés actifs depuis Oracle
            $employees = $this->getActiveEmployeesFromOracle($limit);
            
            $this->info("Employés trouvés dans Oracle: " . count($employees));

            $imported = 0;
            $existing = 0;
            $errors = 0;

            $bar = $this->output->createProgressBar(count($employees));
            $bar->start();

            foreach ($employees as $employee) {
                try {
                    $matricule = $employee->matricule ?? $employee->employee_number ?? null;
                    
                    if (!$matricule) {
                        $bar->advance();
                        continue;
                    }

                    // Vérifier si l'utilisateur existe déjà
                    $exists = User::where('matricule', $matricule)->exists();

                    if ($exists) {
                        $existing++;
                    } else {
                        // Récupérer les données complètes
                        $oracleData = $this->oracleService->getEmployeeByMatricule($matricule);
                        
                        if ($oracleData && !$dryRun) {
                            $this->createUserFromOracle($oracleData);
                            $imported++;
                        } elseif ($oracleData) {
                            $imported++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::warning("Import error for employee: " . $e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("=== Résumé de l'importation ===");
            $this->table(
                ['Statut', 'Nombre'],
                [
                    ['Importés', $imported],
                    ['Déjà existants', $existing],
                    ['Erreurs', $errors],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erreur lors de l'importation: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Importer TOUS les employés depuis Oracle avec complétion LDAP
     * Matricule = Oracle (plus fiable)
     * Si champ manquant dans Oracle => chercher dans LDAP
     * Photos => LDAP
     */
    protected function importAllFromOracleWithLdap(bool $dryRun = false): int
    {
        $batchSize = (int) $this->option('batch');
        
        $this->initSyncLog('Importation Massive Oracle + LDAP');
        $this->info("=== IMPORTATION MASSIVE DEPUIS ORACLE + LDAP ===");
        $this->logProgress("=== IMPORTATION MASSIVE DEPUIS ORACLE + LDAP ===");
        $this->info("Priorité: Matricule d'Oracle (plus fiable)");
        $this->info("Complétion des champs manquants depuis LDAP");
        $this->info("Photos récupérées depuis LDAP");
        $this->newLine();

        // Étape 1: Compter tous les employés actifs dans Oracle
        $this->info("Comptage des employés actifs dans Oracle...");
        $this->logProgress("Comptage des employés actifs dans Oracle...");
        $totalCount = $this->countActiveEmployeesInOracle();
        
        if ($totalCount === 0) {
            $this->error("Aucun employé trouvé dans Oracle ou erreur de connexion.");
            $this->logProgress("❌ Aucun employé trouvé dans Oracle ou erreur de connexion.", "error");
            return Command::FAILURE;
        }
        
        $this->info("Total des employés actifs dans Oracle: {$totalCount}");
        $this->logProgress("Total des employés actifs dans Oracle: {$totalCount}");
        $this->newLine();

        // Créer le dossier profil si nécessaire
        $profileDir = public_path('profil');
        if (!file_exists($profileDir) && !$dryRun) {
            mkdir($profileDir, 0755, true);
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $photosImported = 0;
        $offset = 0;

        // Traiter par lots
        while ($offset < $totalCount) {
            $batchEnd = min($offset + $batchSize, $totalCount);
            $this->info("Récupération du lot {$offset} - {$batchEnd} / {$totalCount}");
            $this->logProgress("📦 Lot " . (int)(($offset / $batchSize) + 1) . " : {$offset} → {$batchEnd} / {$totalCount}");
            
            try {
                // Récupérer les employés Oracle par lot
                $employees = $this->oracleService->getAllEmployees($batchSize, $offset);
                
                if (empty($employees)) {
                    $this->warn("Lot vide, fin de l'importation.");
                    break;
                }

                $bar = $this->output->createProgressBar(count($employees));
                $bar->start();

                $processedInBatch = 0;
                foreach ($employees as $oracleData) {
                    try {
                        $matricule = $oracleData['matricule'] ?? null;
                        
                        if (!$matricule) {
                            $skipped++;
                            $bar->advance();
                            $processedInBatch++;
                            continue;
                        }

                        // Chercher les données LDAP pour compléter
                        $ldapData = $this->findUserInLdap(
                            $matricule,
                            $oracleData['nom'] ?? '',
                            $oracleData['prenom'] ?? ''
                        );

                        // Fusionner les données (Oracle prioritaire, LDAP complète)
                        $mergedData = $this->mergeOracleAndLdapData($oracleData, $ldapData);

                        // Vérifier si l'utilisateur existe
                        $existingUser = User::where('matricule', $matricule)->first();

                        if ($existingUser) {
                            // Mettre à jour
                            if (!$dryRun) {
                                $this->updateUserWithMergedData($existingUser, $mergedData);
                                
                                // Importer photo si manquante
                                if (empty($existingUser->photo) && !empty($ldapData['photo_binary'])) {
                                    $photoPath = $this->savePhotoToFile($matricule, $ldapData['photo_binary']);
                                    if ($photoPath) {
                                        $existingUser->update(['photo' => $photoPath]);
                                        $photosImported++;
                                    }
                                }
                            }
                            $updated++;
                        } else {
                            // Créer
                            if (!$dryRun) {
                                $user = $this->createUserWithMergedData($mergedData);
                                
                                // Importer photo
                                if (!empty($ldapData['photo_binary'])) {
                                    $photoPath = $this->savePhotoToFile($matricule, $ldapData['photo_binary']);
                                    if ($photoPath) {
                                        $user->update(['photo' => $photoPath]);
                                        $photosImported++;
                                    }
                                }
                            }
                            $imported++;
                        }

                    } catch (\Exception $e) {
                        $errors++;
                        Log::warning("Import error for {$matricule}: " . $e->getMessage());
                    }

                    $bar->advance();
                    $processedInBatch++;

                    // Log détaillé tous les 50 utilisateurs pour le suivi en temps réel
                    $totalProcessed = $offset + $processedInBatch;
                    if ($processedInBatch % 50 === 0 || $processedInBatch === count($employees)) {
                        $this->logProgress("📊 {$totalProcessed}/{$totalCount} traités — {$imported} importés, {$updated} mis à jour, {$photosImported} photos");
                        $this->updateProgress($totalProcessed, $totalCount);
                    }
                }

                $bar->finish();
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->error("Erreur lors de la récupération du lot: " . $e->getMessage());
                $errors++;
            }

            $offset += $batchSize;
            
            // Mettre à jour la progression
            $currentProgress = min($offset, $totalCount);
            $this->updateProgress($currentProgress, $totalCount);
            $this->logProgress("Progression: {$currentProgress} / {$totalCount} ({$imported} importés, {$updated} mis à jour)");
            
            // Pause pour éviter de surcharger les serveurs
            usleep(100000); // 100ms
        }

        $this->newLine(2);
        $summary = "Importés: {$imported}, Mis à jour: {$updated}, Photos: {$photosImported}, Erreurs: {$errors}";
        $this->info("=== RÉSUMÉ DE L'IMPORTATION MASSIVE ===");
        $this->logProgress("=== RÉSUMÉ DE L'IMPORTATION MASSIVE ===", "success");
        $this->logProgress("Nouveaux utilisateurs importés: {$imported}");
        $this->logProgress("Utilisateurs mis à jour: {$updated}");
        $this->logProgress("Photos importées depuis LDAP: {$photosImported}");
        $this->logProgress("Ignorés (sans matricule): {$skipped}");
        $this->logProgress("Erreurs: {$errors}", $errors > 0 ? "warning" : "info");
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Nouveaux utilisateurs importés', $imported],
                ['Utilisateurs mis à jour', $updated],
                ['Photos importées depuis LDAP', $photosImported],
                ['Ignorés (sans matricule)', $skipped],
                ['Erreurs', $errors],
                ['Total traité', $imported + $updated + $skipped + $errors],
            ]
        );
        
        $this->endSyncLog($summary);

        return Command::SUCCESS;
    }

    /**
     * Compter tous les employés actifs dans Oracle
     */
    protected function countActiveEmployeesInOracle(): int
    {
        try {
            $sql = "
                SELECT COUNT(DISTINCT pop.employee_number) as total
                FROM per_all_people_f pop,
                     per_all_assignments_f paaf,
                     per_periods_of_service pos
                WHERE pop.person_id = paaf.person_id
                  AND pop.person_id = pos.person_id
                  AND pos.actual_termination_date IS NULL
                  AND paaf.assignment_status_type_id IN ('1', '46', '45', '43', '2', '44', '5')
                  AND SYSDATE BETWEEN pop.effective_start_date AND pop.effective_end_date
                  AND SYSDATE BETWEEN paaf.effective_start_date AND paaf.effective_end_date
                  AND pop.employee_number IS NOT NULL
            ";

            $result = DB::connection('oracle')->select($sql);
            return $result[0]->total ?? 0;

        } catch (\Exception $e) {
            Log::error("Error counting Oracle employees: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Fusionner les données Oracle et LDAP (Oracle prioritaire)
     */
    protected function mergeOracleAndLdapData(array $oracleData, ?array $ldapData): array
    {
        $merged = $oracleData;

        if ($ldapData) {
            // Email: Oracle > LDAP > fallback
            if (empty($merged['email']) && !empty($ldapData['email'])) {
                $merged['email'] = $ldapData['email'];
            }
            
            // Compléter les champs manquants avec LDAP
            $ldapFields = [
                'ldap_username' => 'ldap_username',
                'telephone' => 'telephone',
                'entreprise' => 'entreprise',
                'title' => 'poste_ldap',
                'departement_ldap' => 'departement_ldap',
            ];

            foreach ($ldapFields as $ldapKey => $mergedKey) {
                if (!empty($ldapData[$ldapKey])) {
                    $merged[$mergedKey] = $ldapData[$ldapKey];
                }
            }
        }

        // Email fallback si toujours vide
        if (empty($merged['email'])) {
            $merged['email'] = $merged['matricule'] . '@senelec.sn';
        }

        return $merged;
    }

    /**
     * Créer un utilisateur avec les données fusionnées Oracle+LDAP
     */
    protected function createUserWithMergedData(array $data): User
    {
        return User::create([
            'matricule' => $data['matricule'],
            'name' => trim(($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? '')),
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? null,
            'email' => $data['email'],
            'password' => bcrypt('Senelec@' . $data['matricule']),
            
            // Données Oracle
            'poste' => $data['poste'] ?? $data['fonction'] ?? $data['poste_ldap'] ?? null,
            'fonction_oracle' => $data['fonction'] ?? null,
            'grade_fonction' => $data['grade_fonction'] ?? null,
            'niveau_remuneration' => $data['niveau_remuneration'] ?? null,
            'college' => $data['college'] ?? null,
            'organisation' => $data['organisation'] ?? null,
            'oracle_person_id' => $data['person_id'] ?? null,
            'oracle_org_id' => $data['organization_id'] ?? null,
            'centre_responsabilite' => $data['centre_responsabilite'] ?? null,
            'localisation' => $data['localisation'] ?? $data['lieu'] ?? null,
            
            // Hiérarchie Oracle
            'direction_generale' => $data['direction_generale'] ?? null,
            'direction_principale' => $data['direction_principale'] ?? null,
            'direction' => $data['direction'] ?? null,
            'delegation' => $data['delegation'] ?? null,
            'departement' => $data['departement'] ?? $data['departement_ldap'] ?? null,
            'service' => $data['service'] ?? null,
            
            // Données LDAP
            'ldap_username' => $data['ldap_username'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'entreprise' => $data['entreprise'] ?? 'SENELEC',
            
            // Métadonnées
            'oracle_synced_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Mettre à jour un utilisateur avec les données fusionnées Oracle+LDAP
     */
    protected function updateUserWithMergedData(User $user, array $data): void
    {
        $updateData = [
            // Données de base (toujours mettre à jour depuis Oracle)
            'nom' => $data['nom'] ?? $user->nom,
            'prenom' => $data['prenom'] ?? $user->prenom,
            'name' => trim(($data['prenom'] ?? $user->prenom ?? '') . ' ' . ($data['nom'] ?? $user->nom ?? '')),
            
            // Email: mettre à jour si c'était un fake
            'email' => $data['email'] ?? $user->email,
            
            // Données Oracle
            'poste' => $data['poste'] ?? $data['fonction'] ?? $user->poste,
            'fonction_oracle' => $data['fonction'] ?? $user->fonction_oracle,
            'grade_fonction' => $data['grade_fonction'] ?? $user->grade_fonction,
            'niveau_remuneration' => $data['niveau_remuneration'] ?? $user->niveau_remuneration,
            'college' => $data['college'] ?? $user->college,
            'organisation' => $data['organisation'] ?? $user->organisation,
            'oracle_person_id' => $data['person_id'] ?? $user->oracle_person_id,
            'oracle_org_id' => $data['organization_id'] ?? $user->oracle_org_id,
            'centre_responsabilite' => $data['centre_responsabilite'] ?? $user->centre_responsabilite,
            'localisation' => $data['localisation'] ?? $data['lieu'] ?? $user->localisation,
            
            // Hiérarchie Oracle
            'direction_generale' => $data['direction_generale'] ?? $user->direction_generale,
            'direction_principale' => $data['direction_principale'] ?? $user->direction_principale,
            'direction' => $data['direction'] ?? $user->direction,
            'delegation' => $data['delegation'] ?? $user->delegation,
            'departement' => $data['departement'] ?? $data['departement_ldap'] ?? $user->departement,
            'service' => $data['service'] ?? $user->service,
            
            // Métadonnées
            'oracle_synced_at' => now(),
        ];

        // Données LDAP (seulement si vides)
        if (empty($user->ldap_username) && !empty($data['ldap_username'])) {
            $updateData['ldap_username'] = $data['ldap_username'];
        }
        if (empty($user->telephone) && !empty($data['telephone'])) {
            $updateData['telephone'] = $data['telephone'];
        }
        if (empty($user->entreprise) && !empty($data['entreprise'])) {
            $updateData['entreprise'] = $data['entreprise'];
        }

        $user->update($updateData);
    }

    /**
     * Récupérer les employés actifs depuis Oracle
     */
    protected function getActiveEmployeesFromOracle(int $limit = 100): array
    {
        try {
            $sql = "
                SELECT DISTINCT
                    pop.employee_number as matricule,
                    pop.first_name as prenom,
                    pop.last_name as nom,
                    pop.email_address as email
                FROM
                    per_all_people_f pop,
                    per_all_assignments_f paaf
                WHERE
                    pop.person_id = paaf.person_id
                    AND paaf.assignment_status_type_id IN ('1', '46', '45', '43', '2', '44', '5')
                    AND SYSDATE BETWEEN pop.effective_start_date AND pop.effective_end_date
                    AND SYSDATE BETWEEN paaf.effective_start_date AND paaf.effective_end_date
                    AND pop.employee_number IS NOT NULL
                    AND ROWNUM <= :limit
                ORDER BY pop.employee_number
            ";

            return DB::connection('oracle')->select($sql, ['limit' => $limit]);

        } catch (\Exception $e) {
            Log::error("Error fetching Oracle employees: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Créer un utilisateur à partir des données Oracle
     */
    protected function createUserFromOracle(array $oracleData): User
    {
        // Chercher les données dans LDAP si non présentes dans Oracle
        $ldapData = $this->findUserInLdap(
            $oracleData['matricule'], 
            $oracleData['nom'] ?? '', 
            $oracleData['prenom'] ?? ''
        );
        
        // Email: Oracle > LDAP > fallback
        $email = $oracleData['email'] ?? null;
        if (empty($email) && $ldapData) {
            $email = $ldapData['email'] ?? null;
        }
        if (empty($email)) {
            $email = $oracleData['matricule'] . '@senelec.sn';
        }

        return User::create([
            'matricule' => $oracleData['matricule'],
            'name' => trim(($oracleData['prenom'] ?? '') . ' ' . ($oracleData['nom'] ?? '')),
            'nom' => $oracleData['nom'] ?? null,
            'prenom' => $oracleData['prenom'] ?? null,
            'email' => $email,
            'password' => bcrypt('Senelec@' . $oracleData['matricule']),
            'poste' => $oracleData['poste'] ?? $oracleData['fonction'] ?? $ldapData['title'] ?? null,
            'fonction_oracle' => $oracleData['fonction'] ?? null,
            'organisation' => $oracleData['organisation'] ?? null,
            'service' => $oracleData['service'] ?? null,
            'direction' => $oracleData['direction'] ?? $oracleData['direction_principale'] ?? null,
            'departement' => $oracleData['departement'] ?? $ldapData['departement_ldap'] ?? null,
            'oracle_person_id' => $oracleData['person_id'] ?? null,
            'oracle_synced_at' => now(),
            'is_active' => true,
            // Données LDAP
            'ldap_username' => $ldapData['ldap_username'] ?? null,
            'telephone' => $ldapData['telephone'] ?? null,
            'photo' => $ldapData['photo'] ?? null,
            'entreprise' => $ldapData['entreprise'] ?? null,
        ]);
    }

    /**
     * Mettre à jour un utilisateur à partir des données Oracle
     */
    protected function updateUserFromOracle(User $user, array $oracleData): void
    {
        // Chercher les données dans LDAP si besoin
        $ldapData = null;
        if (empty($user->ldap_username) || empty($user->telephone) || empty($user->entreprise)) {
            $ldapData = $this->findUserInLdap(
                $oracleData['matricule'], 
                $oracleData['nom'] ?? $user->nom ?? '', 
                $oracleData['prenom'] ?? $user->prenom ?? ''
            );
        }
        
        $updateData = [
            'nom' => $oracleData['nom'] ?? $user->nom,
            'prenom' => $oracleData['prenom'] ?? $user->prenom,
            'name' => trim(($oracleData['prenom'] ?? $user->prenom ?? '') . ' ' . ($oracleData['nom'] ?? $user->nom ?? '')),
            'email' => $oracleData['email'] ?? $user->email,
            'poste' => $oracleData['poste'] ?? $oracleData['fonction'] ?? $user->poste,
            'fonction_oracle' => $oracleData['fonction'] ?? $user->fonction_oracle,
            'organisation' => $oracleData['organisation'] ?? $user->organisation,
            'service' => $oracleData['service'] ?? $user->service,
            'direction' => $oracleData['direction'] ?? $oracleData['direction_principale'] ?? $user->direction,
            'departement' => $oracleData['departement'] ?? $user->departement,
            'oracle_person_id' => $oracleData['person_id'] ?? $user->oracle_person_id,
            'oracle_synced_at' => now(),
        ];
        
        // Ajouter les données LDAP si trouvées et si pas déjà remplies
        if ($ldapData) {
            if (empty($user->ldap_username) && !empty($ldapData['ldap_username'])) {
                $updateData['ldap_username'] = $ldapData['ldap_username'];
            }
            if (empty($user->telephone) && !empty($ldapData['telephone'])) {
                $updateData['telephone'] = $ldapData['telephone'];
            }
            if (empty($user->photo) && !empty($ldapData['photo_binary'])) {
                // Sauvegarder la photo en fichier
                $photoPath = $this->savePhotoToFile($user->matricule, $ldapData['photo_binary']);
                if ($photoPath) {
                    $updateData['photo'] = $photoPath;
                }
            }
            if (empty($user->entreprise) && !empty($ldapData['entreprise'])) {
                $updateData['entreprise'] = $ldapData['entreprise'];
            }
            if (empty($user->departement) && !empty($ldapData['departement_ldap'])) {
                $updateData['departement'] = $ldapData['departement_ldap'];
            }
        }
        
        $user->update($updateData);
    }

    /**
     * Afficher les données Oracle de façon formatée
     */
    protected function displayOracleData(array $data): void
    {
        $this->newLine();
        $this->info("Données Oracle trouvées:");
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Matricule', $data['matricule'] ?? '-'],
                ['Nom', $data['nom'] ?? '-'],
                ['Prénom', $data['prenom'] ?? '-'],
                ['Email', $data['email'] ?? '-'],
                ['Fonction', $data['fonction'] ?? '-'],
                ['Poste', $data['poste'] ?? '-'],
                ['Organisation', $data['organisation'] ?? '-'],
                ['Service', $data['service'] ?? '-'],
                ['Direction', $data['direction'] ?? '-'],
                ['Département', $data['departement'] ?? '-'],
                ['Localisation', $data['localisation'] ?? '-'],
            ]
        );
        $this->newLine();
    }

    /**
     * Chercher l'email d'un utilisateur dans LDAP
     */
    protected function findEmailInLdap(string $matricule, string $nom = '', string $prenom = ''): ?string
    {
        $ldapData = $this->findUserInLdap($matricule, $nom, $prenom);
        return $ldapData['email'] ?? null;
    }

    /**
     * Chercher toutes les données d'un utilisateur dans LDAP
     */
    protected function findUserInLdap(string $matricule, string $nom = '', string $prenom = ''): ?array
    {
        try {
            $connection = Container::getConnection('default');
            $baseDn = $connection->getConfiguration()->get('base_dn');
            $query = $connection->query();
            $entry = null;
            
            // Recherche par matricule dans le champ company (format: "SENELEC M05630")
            $results = $query->setDn($baseDn)
                ->rawFilter('(&(objectClass=person)(company=*' . ldap_escape($matricule, '', LDAP_ESCAPE_FILTER) . '))')
                ->limit(1)
                ->get();
            
            if (!empty($results)) {
                $entry = $results[0];
            }
            
            // Recherche par nom + prénom si matricule non trouvé
            if (!$entry && !empty($nom) && !empty($prenom)) {
                $query = $connection->query();
                $results = $query->setDn($baseDn)
                    ->rawFilter('(&(objectClass=person)(sn=' . ldap_escape($nom, '', LDAP_ESCAPE_FILTER) . ')(givenName=' . ldap_escape($prenom, '', LDAP_ESCAPE_FILTER) . '))')
                    ->limit(1)
                    ->get();
                
                if (!empty($results)) {
                    $entry = $results[0];
                }
            }

            // Recherche par cn (Common Name) contenant le nom
            if (!$entry && !empty($nom)) {
                $query = $connection->query();
                $results = $query->setDn($baseDn)
                    ->rawFilter('(&(objectClass=person)(cn=*' . ldap_escape($nom, '', LDAP_ESCAPE_FILTER) . '*))')
                    ->limit(1)
                    ->get();
                
                if (!empty($results)) {
                    $entry = $results[0];
                }
            }

            if (!$entry) {
                return null;
            }

            // Extraire toutes les informations LDAP
            $data = [];
            
            // Email
            if (isset($entry['mail'])) {
                $data['email'] = is_array($entry['mail']) ? $entry['mail'][0] : $entry['mail'];
            }
            
            // Username LDAP (sAMAccountName ou uid)
            if (isset($entry['samaccountname'])) {
                $data['ldap_username'] = is_array($entry['samaccountname']) ? $entry['samaccountname'][0] : $entry['samaccountname'];
            } elseif (isset($entry['uid'])) {
                $data['ldap_username'] = is_array($entry['uid']) ? $entry['uid'][0] : $entry['uid'];
            }
            
            // Téléphone
            if (isset($entry['telephonenumber'])) {
                $data['telephone'] = is_array($entry['telephonenumber']) ? $entry['telephonenumber'][0] : $entry['telephonenumber'];
            } elseif (isset($entry['mobile'])) {
                $data['telephone'] = is_array($entry['mobile']) ? $entry['mobile'][0] : $entry['mobile'];
            }
            
            // Photo - garder les données binaires pour sauvegarder en fichier
            if (isset($entry['thumbnailphoto'])) {
                $photoData = is_array($entry['thumbnailphoto']) ? $entry['thumbnailphoto'][0] : $entry['thumbnailphoto'];
                $data['photo_binary'] = $photoData;
            } elseif (isset($entry['jpegphoto'])) {
                $photoData = is_array($entry['jpegphoto']) ? $entry['jpegphoto'][0] : $entry['jpegphoto'];
                $data['photo_binary'] = $photoData;
            }
            
            // Entreprise (company sans le matricule)
            if (isset($entry['company'])) {
                $company = is_array($entry['company']) ? $entry['company'][0] : $entry['company'];
                // Retirer "SENELEC" et le matricule pour obtenir juste "SENELEC"
                $data['entreprise'] = trim(preg_replace('/\s*' . preg_quote($matricule, '/') . '\s*/', '', $company));
                if (empty($data['entreprise'])) {
                    $data['entreprise'] = 'SENELEC';
                }
            }
            
            // Département (si pas déjà rempli par Oracle)
            if (isset($entry['department'])) {
                $data['departement_ldap'] = is_array($entry['department']) ? $entry['department'][0] : $entry['department'];
            }
            
            // Titre/Fonction
            if (isset($entry['title'])) {
                $data['title'] = is_array($entry['title']) ? $entry['title'][0] : $entry['title'];
            }

            return $data;

        } catch (\Exception $e) {
            Log::debug("LDAP search error for {$matricule}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sauvegarder la photo LDAP en fichier (storage/app/public/profil pour persistance Docker)
     */
    protected function savePhotoToFile(string $matricule, string $photoBinary): ?string
    {
        try {
            if (empty($matricule) || empty($photoBinary)) {
                return null;
            }

            $imagePath = 'profil/' . $matricule . '.jpg';
            $fullPath = public_path($imagePath);

            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            file_put_contents($fullPath, $photoBinary);

            return $imagePath;

        } catch (\Exception $e) {
            Log::debug("Error saving photo for {$matricule}: " . $e->getMessage());
            return null;
        }
    }
}
