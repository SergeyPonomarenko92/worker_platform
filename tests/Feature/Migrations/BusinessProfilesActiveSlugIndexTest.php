<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessProfilesActiveSlugIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profiles_active_slug_index_exists_on_postgres(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'pgsql') {
            $this->markTestSkipped("Index is Postgres-only; current driver: {$driver}");
        }

        $indexes = collect(DB::select(
            'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
            ['business_profiles']
        ))->pluck('indexname')->all();

        $this->assertContains('business_profiles_active_slug_idx', $indexes);
    }
}
