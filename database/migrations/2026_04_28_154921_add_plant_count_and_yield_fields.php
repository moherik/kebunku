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
        Schema::table('crops', function (Blueprint $table) {
            $table->decimal('estimated_yield_per_plant_kg', 8, 2)->nullable()->after('default_hst');
        });

        Schema::table('plantings', function (Blueprint $table) {
            $table->integer('plant_count')->nullable()->after('area_hectares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            $table->dropColumn('plant_count');
        });

        Schema::table('crops', function (Blueprint $table) {
            $table->dropColumn('estimated_yield_per_plant_kg');
        });
    }
};
