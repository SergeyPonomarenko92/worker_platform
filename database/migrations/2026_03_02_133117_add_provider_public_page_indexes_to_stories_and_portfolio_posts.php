<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Provider public page frequently queries these tables by (business_profile_id + time).
        // Add composite indexes to keep lookups bounded as data grows.

        $driver = Schema::getConnection()->getDriverName();

        $indexExists = function (string $tableName, string $indexName) use ($driver): bool {
            if ($driver === 'pgsql') {
                return ! empty(DB::select(
                    'select 1 from pg_indexes where schemaname = current_schema() and tablename = ? and indexname = ?',
                    [$tableName, $indexName]
                ));
            }

            if ($driver === 'mysql') {
                $database = DB::getDatabaseName();

                return ! empty(DB::select(
                    'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
                    [$database, $tableName, $indexName]
                ));
            }

            if ($driver === 'sqlite') {
                // PRAGMA returns: seq, name, unique, origin, partial
                return collect(DB::select("pragma index_list('{$tableName}')"))
                    ->pluck('name')
                    ->contains($indexName);
            }

            // Unknown driver: best-effort (assume doesn't exist). We avoid trying to be clever here.
            return false;
        };

        Schema::table('stories', function (Blueprint $table) use ($indexExists) {
            // The base create-table migration already adds this composite index.
            // When running migrations from scratch, adding it again would fail.
            // Drop the auto-generated index name first (if present), then re-add with an explicit name.
            $autoIndex = 'stories_business_profile_id_expires_at_index';
            $explicitIndex = 'stories_bp_expires_at_index';

            if ($indexExists('stories', $explicitIndex)) {
                return;
            }

            if ($indexExists('stories', $autoIndex)) {
                $table->dropIndex($autoIndex);
            }

            $table->index(['business_profile_id', 'expires_at'], $explicitIndex);
        });

        Schema::table('portfolio_posts', function (Blueprint $table) use ($indexExists) {
            // The base create-table migration already adds this composite index.
            // When running migrations from scratch, adding it again would fail.
            // Drop the auto-generated index name first (if present), then re-add with an explicit name.
            $autoIndex = 'portfolio_posts_business_profile_id_published_at_index';
            $explicitIndex = 'portfolio_posts_bp_published_at_index';

            if ($indexExists('portfolio_posts', $explicitIndex)) {
                return;
            }

            if ($indexExists('portfolio_posts', $autoIndex)) {
                $table->dropIndex($autoIndex);
            }

            $table->index(['business_profile_id', 'published_at'], $explicitIndex);
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        $indexExists = function (string $tableName, string $indexName) use ($driver): bool {
            if ($driver === 'pgsql') {
                return ! empty(DB::select(
                    'select 1 from pg_indexes where schemaname = current_schema() and tablename = ? and indexname = ?',
                    [$tableName, $indexName]
                ));
            }

            if ($driver === 'mysql') {
                $database = DB::getDatabaseName();

                return ! empty(DB::select(
                    'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
                    [$database, $tableName, $indexName]
                ));
            }

            if ($driver === 'sqlite') {
                return collect(DB::select("pragma index_list('{$tableName}')"))
                    ->pluck('name')
                    ->contains($indexName);
            }

            return false;
        };

        Schema::table('stories', function (Blueprint $table) use ($indexExists) {
            $explicitIndex = 'stories_bp_expires_at_index';
            if ($indexExists('stories', $explicitIndex)) {
                $table->dropIndex($explicitIndex);
            }

            // Restore the default (auto-named) index if it's missing.
            $autoIndex = 'stories_business_profile_id_expires_at_index';
            if (! $indexExists('stories', $autoIndex)) {
                $table->index(['business_profile_id', 'expires_at']);
            }
        });

        Schema::table('portfolio_posts', function (Blueprint $table) use ($indexExists) {
            $explicitIndex = 'portfolio_posts_bp_published_at_index';
            if ($indexExists('portfolio_posts', $explicitIndex)) {
                $table->dropIndex($explicitIndex);
            }

            // Restore the default (auto-named) index if it's missing.
            $autoIndex = 'portfolio_posts_business_profile_id_published_at_index';
            if (! $indexExists('portfolio_posts', $autoIndex)) {
                $table->index(['business_profile_id', 'published_at']);
            }
        });
    }
};
