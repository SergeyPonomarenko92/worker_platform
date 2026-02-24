<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: help most common catalog sort/filter patterns.
        // - newest: order by created_at desc with is_active=true
        // - join business_profiles on is_active=true

        DB::statement('create index if not exists offers_active_created_at_idx on offers (is_active, created_at desc)');
        DB::statement('create index if not exists business_profiles_is_active_idx on business_profiles (is_active)');
    }

    public function down(): void
    {
        DB::statement('drop index if exists offers_active_created_at_idx');
        DB::statement('drop index if exists business_profiles_is_active_idx');
    }
};
