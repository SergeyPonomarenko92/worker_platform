<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historical note:
        // - 2026_02_25_* created index name: deals_bp_client_status_completed_at_index
        // - 2026_02_27_* created (or intended) index name: deals_bp_client_status_completed_at_idx
        // Some environments ended up with BOTH indexes (same columns), which is redundant.
        // This migration keeps a single canonical index and removes the duplicate when present.

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
                // PRAGMA returns: seq, name, unique, origin, partial
                return collect(DB::select("pragma index_list('deals')"))
                    ->pluck('name')
                    ->contains($indexName);
            }

            // Unknown driver: best-effort (assume doesn't exist). We avoid trying to be clever here.
            return false;
        };

        $shortName = 'deals_bp_client_status_completed_at_idx';
        $longName = 'deals_bp_client_status_completed_at_index';

        $hasShort = $indexExists($shortName);
        $hasLong = $indexExists($longName);

        // If both exist, drop the older/longer one and keep the canonical short name.
        if ($hasShort && $hasLong) {
            Schema::table('deals', function (Blueprint $table) use ($longName) {
                $table->dropIndex($longName);
            });

            return;
        }

        // If only the legacy long name exists, replace it with the canonical short name.
        if (! $hasShort && $hasLong) {
            Schema::table('deals', function (Blueprint $table) use ($longName) {
                $table->dropIndex($longName);
            });

            Schema::table('deals', function (Blueprint $table) use ($shortName) {
                $table->index(
                    ['business_profile_id', 'client_user_id', 'status', 'completed_at'],
                    $shortName
                );
            });

            return;
        }

        // If neither exists, create the canonical index.
        if (! $hasShort && ! $hasLong) {
            Schema::table('deals', function (Blueprint $table) use ($shortName) {
                $table->index(
                    ['business_profile_id', 'client_user_id', 'status', 'completed_at'],
                    $shortName
                );
            });
        }
    }

    public function down(): void
    {
        // No-op: this migration is a cleanup / idempotency hardening.
        // We intentionally don't re-create a removed duplicate index on rollback.
    }
};
