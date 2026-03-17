<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // Enrichir la table services avec données Oracle
        // ==========================================
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('oracle_org_id')->nullable()->unique()->after('id');
            $table->string('code')->nullable()->after('libelle');
            $table->string('centre_responsabilite')->nullable()->after('code');
            $table->string('type')->default('SER')->after('centre_responsabilite'); // SER
            $table->boolean('is_active')->default(true)->after('type');
        });

        // ==========================================
        // Enrichir la table sites avec données Oracle
        // ==========================================
        Schema::table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('oracle_location_id')->nullable()->unique()->after('id');
            $table->string('code')->nullable()->after('libelle');
            $table->string('adresse')->nullable()->after('code');
            $table->string('ville')->nullable()->after('adresse');
            $table->string('region')->nullable()->after('ville');
            $table->boolean('is_active')->default(true)->after('region');
        });

        // ==========================================
        // Table directions (DG, DIRP, DIR)
        // ==========================================
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oracle_org_id')->nullable()->unique();
            $table->string('libelle');
            $table->string('code')->nullable();
            $table->string('centre_responsabilite')->nullable();
            $table->string('type'); // DG, DIRP, DIR
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ==========================================
        // Table départements (DEP)
        // ==========================================
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oracle_org_id')->nullable()->unique();
            $table->string('libelle');
            $table->string('code')->nullable();
            $table->string('centre_responsabilite')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ==========================================
        // Table délégations (DEL)
        // ==========================================
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oracle_org_id')->nullable()->unique();
            $table->string('libelle');
            $table->string('code')->nullable();
            $table->string('centre_responsabilite')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegations');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('directions');

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['oracle_location_id', 'code', 'adresse', 'ville', 'region', 'is_active']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['oracle_org_id', 'code', 'centre_responsabilite', 'type', 'is_active']);
        });
    }
};
