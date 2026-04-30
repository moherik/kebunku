<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'watering',       // Penyiraman
                'fertilizing',    // Pemupukan
                'spraying',       // Penyemprotan
                'weeding',        // Penyiangan
                'pruning',        // Pemangkasan
                'harvesting',     // Panen
                'seeding',        // Persemaian
                'transplanting',  // Pindah tanam
                'soil_prep',      // Olah tanah
                'other',          // Lainnya
            ]);
            $table->date('activity_date');
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('quantity_unit')->nullable();
            $table->timestamps();

            $table->index(['planting_id', 'activity_date']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
