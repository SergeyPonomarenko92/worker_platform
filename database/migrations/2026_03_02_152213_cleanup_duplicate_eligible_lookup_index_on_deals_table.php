<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cleanup:
        // We historically ended up with multiple indexes that all target the same lookup pattern
        // (business_profile_id, client_user_id, status, completed_at).
        // Keeping duplicates is wasteful (slower writes, bigger DB) and brings no benefit.
        //
        // Canonical index (kept): deals_bp_client_status_completed_at_idx (see 2026_03_02_132120_*)
        // Duplicate (drop if present): deals_eligible_review_lookup_idx (see 2026_03_02_143547_*)

        $driver = Schema::getConnection()->getDriverName();

        $indexExists = function (string $indexName) use ($driver): bool {
            if ($driver === 'pgsql') {
                return ! empty(DB::select(
                    'select 1 from pg_indexes where schemaname = current_schema() and indexname = ?',
                    [$indexName]
                ));
            }

            if ($driver === 'mysql') {
                $database = DB::getDatabaseName();

                return ! empty(DB::select(
                    'select 1 from information_schema.statistics where table_schema = ? and index_name = ? limit 1',
                    [$database, $indexName]
                ));
            }

            if ($driver === 'sqlite') {
                return collect(DB::select("pragma index_list('deals')"))
                    ->pluck('name')
                    ->contains($indexName);
            }

            return false;
        };

        $duplicate = 'deals_eligible_review_lookup_idx';

        if ($indexExists($duplicate)) {
            Schema::table('deals', function (Blueprint $table) use ($duplicate) {
                $table->dropIndex($duplicate);
            });
        }
    }

    public function down(): void
    {
        // No-op: this is a safe cleanup migration.
        // We intentionally do not re-create removed duplicate indexes on rollback.
    }
};
