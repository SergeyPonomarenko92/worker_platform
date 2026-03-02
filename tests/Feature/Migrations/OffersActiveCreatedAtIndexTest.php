<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OffersActiveCreatedAtIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_offers_active_created_at_index_exists_with_explicit_name(): void
    {
        $driver = DB::getDriverName();

        $indexes = match ($driver) {
            'sqlite' => collect(DB::select("pragma index_list('offers')"))->pluck('name')->all(),
            'pgsql' => collect(DB::select(
                'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
                ['offers']
            ))->pluck('indexname')->all(),
            'mysql' => collect(DB::select(
                'select index_name from information_schema.statistics where table_schema = ? and table_name = ?',
                [DB::getDatabaseName(), 'offers']
            ))->pluck('index_name')->all(),
            default => null,
        };

        if ($indexes === null) {
            $this->markTestSkipped("Unsupported driver for index assertions: {$driver}");
        }

        $this->assertContains('offers_active_created_at_index', $indexes);
    }
}
