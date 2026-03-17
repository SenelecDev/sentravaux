<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sites
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->timestamps();
        });

        // Services demandeurs
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->timestamps();
        });

        // Equipes
        Schema::create('equipes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Personnels
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('matricule')->nullable();
            $table->string('organisation')->nullable();
            $table->string('cr')->nullable();
            $table->string('lieu')->nullable();
            $table->string('poste')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Demandes de travaux
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_demande')->unique();
            $table->string('objet')->nullable();
            $table->text('observation')->nullable();
            $table->date('date')->nullable();
            $table->date('date_fin')->nullable();
            $table->date('date_intervention')->nullable();
            $table->datetime('date_debut_intervention')->nullable();
            $table->datetime('date_fin_intervention')->nullable();

            // Demandeur + Approbateur N+1
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approbateur_n1_id')->nullable()->constrained('users')->nullOnDelete();

            // Statut & Nature
            $table->string('statut')->default('brouillon');
            $table->string('nature')->nullable();
            $table->string('unite_code')->nullable();

            // Service & Site demandeur
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();

            // Affectations service → unité
            $table->foreignId('sad_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('seg_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('umt_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ubt_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('unsp_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('umr_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('utgc_id')->nullable()->constrained('users')->nullOnDelete();

            // Équipe d'exécution
            $table->foreignId('chef_equipe_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('superviseur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('executant_id')->nullable()->constrained('users')->nullOnDelete();

            // Type de prestation
            $table->string('type_prestation')->nullable();
            $table->string('team_type')->nullable(); // interne / externe
            $table->string('prestataire_nom')->nullable();
            $table->string('numero_commande')->nullable();
            $table->text('commentaire_prestataire')->nullable();

            // Commentaires par unité
            $table->text('comment_umt')->nullable();
            $table->text('comment_ubt')->nullable();
            $table->text('comment_unsp')->nullable();
            $table->text('comment_umr')->nullable();
            $table->text('comment_utgc')->nullable();
            $table->text('commentaire_equipe')->nullable();

            // Approbation
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire_approbation')->nullable();

            // Rejet niveau 1 (approbateur)
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motif')->nullable();

            // Rejet niveau 2 (SAD/SEG)
            $table->foreignId('rejected_by_n2')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motif2')->nullable();

            // Validation unité
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();

            // Terminaison & Clôture
            $table->foreignId('terminated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cloture_par')->nullable()->constrained('users')->nullOnDelete();

            // Période SEG/UMR
            $table->text('commentaire_periode_seg')->nullable();
            $table->boolean('periode_validee_seg')->default(false);
            $table->boolean('periode_validee_umr')->default(false);

            $table->timestamps();
        });

        // Pivot demande-equipe
        Schema::create('demande_equipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipe_id')->constrained()->cascadeOnDelete();
            $table->decimal('duree', 8, 2)->nullable();
            $table->foreignId('executant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Images des demandes
        Schema::create('demande_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->timestamps();
        });

        // Notifications métier
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('demande_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('demande_images');
        Schema::dropIfExists('demande_equipe');
        Schema::dropIfExists('demandes');
        Schema::dropIfExists('personnels');
        Schema::dropIfExists('equipes');
        Schema::dropIfExists('services');
        Schema::dropIfExists('sites');
    }
};
