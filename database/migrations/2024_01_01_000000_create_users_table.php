<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Identifiants
            $table->string('matricule')->unique()->nullable();
            $table->string('ldap_username')->nullable();
            $table->string('ldap_guid')->nullable()->unique();
            
            // Informations personnelles
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('poste')->nullable();
            $table->string('telephone')->nullable();
            $table->text('photo')->nullable();
            
            // Organisation
            $table->string('organisation')->nullable();
            $table->string('entreprise')->nullable();
            $table->string('service')->nullable();
            $table->string('direction')->nullable();
            $table->string('departement')->nullable();
            
            // Signatures
            $table->string('signature')->nullable();
            $table->string('stamp')->nullable();
            
            // Statut
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
