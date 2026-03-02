<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Provider public page resolves BusinessProfile by slug and also requires is_active=true.
        // A partial index helps keep that lookup fast as the table grows.
        //
        // Postgres-only (ignored on sqlite/mysql).
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("create index if not exists business_profiles_active_slug_idx on business_profiles (slug) where is_active = true");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('drop index if exists business_profiles_active_slug_idx');
    }
};
