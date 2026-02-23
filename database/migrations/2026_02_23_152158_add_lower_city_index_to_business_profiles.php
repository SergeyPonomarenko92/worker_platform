<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Case-insensitive prefix search support for catalog city filter.
        // NOTE: This is Postgres-specific (the project uses Postgres).
        DB::statement('create index if not exists business_profiles_city_lower_idx on business_profiles (lower(city))');
    }

    public function down(): void
    {
        DB::statement('drop index if exists business_profiles_city_lower_idx');
    }
};
