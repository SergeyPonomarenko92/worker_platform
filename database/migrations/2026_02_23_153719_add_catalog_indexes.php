<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: Foreign keys do NOT automatically create indexes.
        // These indexes improve catalog filtering/sorting.

        DB::statement('create index if not exists offers_business_profile_id_idx on offers (business_profile_id)');
        DB::statement('create index if not exists offers_active_price_from_idx on offers (is_active, price_from)');
    }

    public function down(): void
    {
        DB::statement('drop index if exists offers_business_profile_id_idx');
        DB::statement('drop index if exists offers_active_price_from_idx');
    }
};
