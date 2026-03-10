<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = (string) DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $exists = (bool) (DB::selectOne(
                'select 1 as exists from pg_indexes where schemaname = current_schema() and indexname = ? limit 1',
                ['reviews_bp_created_at_index'],
            )?->exists ?? false);

            if ($exists) {
                return;
            }
        }

        Schema::table('reviews', function (Blueprint $table) {
            // Supports provider public page reviews list (latest reviews per provider).
            $table->index(['business_profile_id', 'created_at'], 'reviews_bp_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = (string) DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $exists = (bool) (DB::selectOne(
                'select 1 as exists from pg_indexes where schemaname = current_schema() and indexname = ? limit 1',
                ['reviews_bp_created_at_index'],
            )?->exists ?? false);

            if (! $exists) {
                return;
            }
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_bp_created_at_index');
        });
    }
};
