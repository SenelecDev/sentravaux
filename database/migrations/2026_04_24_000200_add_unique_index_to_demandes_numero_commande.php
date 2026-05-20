<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('demandes')
            ->select('numero_commande', DB::raw('COUNT(*) as total'))
            ->whereNotNull('numero_commande')
            ->where('numero_commande', '!=', '')
            ->groupBy('numero_commande')
            ->having('total', '>', 1)
            ->limit(5)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates->pluck('numero_commande')->implode(', ');
            throw new RuntimeException(
                'Impossible de créer l\'unicité sur numero_commande : des doublons existent déjà. Exemples : ' . $sample
            );
        }

        Schema::table('demandes', function (Blueprint $table) {
            $table->unique('numero_commande', 'demandes_numero_commande_unique');
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropUnique('demandes_numero_commande_unique');
        });
    }
};
