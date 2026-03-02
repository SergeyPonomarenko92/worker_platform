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
        Schema::table('deals', function (Blueprint $table) {
            // Speeds up ProviderController eligible-deal lookup:
            // where business_profile_id, client_user_id, status=completed, completed_at<=now, no review
            // (review check is handled via whereDoesntHave('review')).
            $table->index(
                ['business_profile_id', 'client_user_id', 'status', 'completed_at'],
                'deals_eligible_review_lookup_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_eligible_review_lookup_idx');
        });
    }
};
