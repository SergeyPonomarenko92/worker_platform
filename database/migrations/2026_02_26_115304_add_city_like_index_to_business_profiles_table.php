<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: optimize prefix поиска по місту в каталозі:
        // where lower(city) like 'ки%'
        // Default btree index may not be used efficiently for LIKE without pattern ops.
        DB::statement(
            "create index if not exists business_profiles_city_lower_like_idx on business_profiles ((lower(city)) text_pattern_ops)"
        );
    }

    public function down(): void
    {
        DB::statement('drop index if exists business_profiles_city_lower_like_idx');
    }
};
