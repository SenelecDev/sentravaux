<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes', 'sgb_id')) {
                $table->unsignedBigInteger('sgb_id')->nullable()->after('seg_id');
            }
            if (!Schema::hasColumn('demandes', 'ual_id')) {
                $table->unsignedBigInteger('ual_id')->nullable()->after('utgc_id');
            }
            if (!Schema::hasColumn('demandes', 'ucc_id')) {
                $table->unsignedBigInteger('ucc_id')->nullable()->after('ual_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            foreach (['sgb_id', 'ual_id', 'ucc_id'] as $col) {
                if (Schema::hasColumn('demandes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

