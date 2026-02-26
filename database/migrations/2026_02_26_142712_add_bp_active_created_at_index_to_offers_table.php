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
        Schema::table('offers', function (Blueprint $table) {
            // Speeds up provider public page and cabinet listings:
            // WHERE business_profile_id = ? AND is_active = ? ORDER BY created_at DESC
            $table->index(
                ['business_profile_id', 'is_active', 'created_at'],
                'offers_bp_active_created_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('offers_bp_active_created_at_index');
        });
    }
};
