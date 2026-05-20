<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportReferenceData extends Command
{
    protected $signature = 'reference:export';

    protected $description = 'Exporte sites, services, équipes, directions, départements et délégations vers reference_data.json';

    public function handle(): int
    {
        $script = base_path('database/scripts/export_reference_data.php');

        if (!file_exists($script)) {
            $this->error('Script export introuvable.');

            return self::FAILURE;
        }

        require $script;

        $this->info('Export terminé : database/seeders/data/reference_data.json');

        return self::SUCCESS;
    }
}
