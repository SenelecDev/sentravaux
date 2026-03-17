<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->datetime('date_debut_intervention')->nullable()->change();
            $table->datetime('date_fin_intervention')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->date('date_debut_intervention')->nullable()->change();
            $table->date('date_fin_intervention')->nullable()->change();
        });
    }
};
