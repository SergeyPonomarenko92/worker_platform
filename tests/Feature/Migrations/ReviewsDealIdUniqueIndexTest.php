<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewsDealIdUniqueIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_deal_id_unique_index_exists(): void
    {
        $driver = DB::getDriverName();

        $indexes = match ($driver) {
            'sqlite' => collect(DB::select("pragma index_list('reviews')"))->pluck('name')->all(),
            'pgsql' => collect(DB::select(
                'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
                ['reviews']
            ))->pluck('indexname')->all(),
            'mysql' => collect(DB::select(
                'select index_name from information_schema.statistics where table_schema = ? and table_name = ?',
                [DB::getDatabaseName(), 'reviews']
            ))->pluck('index_name')->all(),
            default => null,
        };

        if ($indexes === null) {
            $this->markTestSkipped("Unsupported driver for index assertions: {$driver}");
        }

        // Laravel default index naming: {table}_{column}_unique.
        $this->assertContains('reviews_deal_id_unique', $indexes);
    }
}
