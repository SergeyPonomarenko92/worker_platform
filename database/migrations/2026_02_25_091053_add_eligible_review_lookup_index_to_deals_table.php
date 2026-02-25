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
            // Helps `ProviderController@show` find the latest completed deal eligible for review
            // with filters on business_profile_id + client_user_id + status and ordering by completed_at.
            $table->index(
                ['business_profile_id', 'client_user_id', 'status', 'completed_at'],
                'deals_bp_client_status_completed_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_bp_client_status_completed_at_index');
        });
    }
};
