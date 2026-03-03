<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessProfilesCityLikeIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profiles_city_lower_like_index_exists_with_explicit_name(): void
    {
        $driver = DB::getDriverName();

        $indexes = match ($driver) {
            'sqlite' => collect(DB::select("pragma index_list('business_profiles')"))->pluck('name')->all(),
            'pgsql' => collect(DB::select(
                'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
                ['business_profiles']
            ))->pluck('indexname')->all(),
            'mysql' => collect(DB::select(
                'select index_name from information_schema.statistics where table_schema = ? and table_name = ?',
                [DB::getDatabaseName(), 'business_profiles']
            ))->pluck('index_name')->all(),
            default => null,
        };

        if ($indexes === null) {
            $this->markTestSkipped("Unsupported driver for index assertions: {$driver}");
        }

        // Added by: 2026_02_26_115304_add_city_like_index_to_business_profiles_table
        $this->assertContains('business_profiles_city_lower_like_idx', $indexes);
    }
}
