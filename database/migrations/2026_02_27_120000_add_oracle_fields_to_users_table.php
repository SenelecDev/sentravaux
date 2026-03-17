<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Oracle HR fields
            $table->string('oracle_person_id')->nullable()->after('departement');
            $table->string('oracle_org_id')->nullable()->after('oracle_person_id');
            $table->string('fonction_oracle')->nullable()->after('oracle_org_id');
            $table->string('grade_fonction')->nullable()->after('fonction_oracle');
            $table->string('niveau_remuneration')->nullable()->after('grade_fonction');
            $table->string('college')->nullable()->after('niveau_remuneration');
            $table->string('centre_responsabilite')->nullable()->after('college');
            $table->string('localisation')->nullable()->after('centre_responsabilite');

            // Hierarchy
            $table->string('direction_generale')->nullable()->after('localisation');
            $table->string('direction_generale_id')->nullable()->after('direction_generale');
            $table->string('direction_principale')->nullable()->after('direction_generale_id');
            $table->string('direction_principale_id')->nullable()->after('direction_principale');
            $table->string('direction_id')->nullable()->after('direction_principale_id');
            $table->string('delegation')->nullable()->after('direction_id');
            $table->string('delegation_id')->nullable()->after('delegation');
            $table->string('departement_id')->nullable()->after('delegation_id');
            $table->string('service_id')->nullable()->after('departement_id');

            // Sync timestamp
            $table->timestamp('oracle_synced_at')->nullable()->after('last_sync_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'oracle_person_id', 'oracle_org_id', 'fonction_oracle',
                'grade_fonction', 'niveau_remuneration', 'college',
                'centre_responsabilite', 'localisation',
                'direction_generale', 'direction_generale_id',
                'direction_principale', 'direction_principale_id',
                'direction_id', 'delegation', 'delegation_id',
                'departement_id', 'service_id', 'oracle_synced_at',
            ]);
        });
    }
};
