<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Speeds up ProviderController@show eligibleDealId lookup:
            // where business_profile_id + client_user_id + status + completed_at and order by completed_at.
            $table->index(
                ['business_profile_id', 'client_user_id', 'status', 'completed_at'],
                'deals_bp_client_status_completed_at_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_bp_client_status_completed_at_idx');
        });
    }
};
