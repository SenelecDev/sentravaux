<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_level', 20); // n1|n2
            $table->text('reason');
            $table->dateTime('rejected_at');
            $table->timestamps();

            $table->index(['demande_id', 'rejected_at']);
            $table->index('rejection_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_rejections');
    }
};
