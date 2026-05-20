<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('demandes') || !Schema::hasColumn('demandes', 'date_intervention')) {
            return;
        }

        $driver = DB::getDriverName();

        // NOTE: On évite ->change() (doctrine/dbal) et on utilise du SQL brut.
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `demandes` MODIFY `date_intervention` DATETIME NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE demandes ALTER COLUMN date_intervention TYPE TIMESTAMP(0) WITHOUT TIME ZONE");
            DB::statement("ALTER TABLE demandes ALTER COLUMN date_intervention DROP NOT NULL");
        } elseif ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE demandes ALTER COLUMN date_intervention DATETIME NULL");
        } else {
            // sqlite ou autre: pas de modification automatique
            // (sqlite nécessite une reconstruction de table)
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('demandes') || !Schema::hasColumn('demandes', 'date_intervention')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `demandes` MODIFY `date_intervention` DATE NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE demandes ALTER COLUMN date_intervention TYPE DATE");
            DB::statement("ALTER TABLE demandes ALTER COLUMN date_intervention DROP NOT NULL");
        } elseif ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE demandes ALTER COLUMN date_intervention DATE NULL");
        } else {
            // sqlite ou autre
        }
    }
};

