<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: helps catalog price filters/sorts.
        // Used patterns:
        // - where is_active = true
        // - where price_from is not null (often)
        // - order by price_from asc/desc
        DB::statement('create index if not exists offers_active_price_from_idx on offers (is_active, price_from)');
    }

    public function down(): void
    {
        DB::statement('drop index if exists offers_active_price_from_idx');
    }
};
