<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: This migration duplicated an earlier index migration (2026_02_25_091053_...),
        // creating two indexes with the same columns on fresh databases.
        // Make it idempotent so we don't create redundant indexes or fail when an index exists.

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

            // Best-effort: unknown driver, let Schema builder handle it.
            return false;
        };

        // If either of the two possible names exists, don't create another index.
        if (
            $indexExists('deals_bp_client_status_completed_at_idx') ||
            $indexExists('deals_bp_client_status_completed_at_index')
        ) {
            return;
        }

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

            return false;
        };

        if (! $indexExists('deals_bp_client_status_completed_at_idx')) {
            return;
        }

        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_bp_client_status_completed_at_idx');
        });
    }
};
