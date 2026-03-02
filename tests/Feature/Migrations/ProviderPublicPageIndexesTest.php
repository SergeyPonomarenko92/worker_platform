<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProviderPublicPageIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_public_page_composite_indexes_have_explicit_names(): void
    {
        $driver = DB::getDriverName();

        $storiesIndexes = match ($driver) {
            'sqlite' => collect(DB::select("pragma index_list('stories')"))->pluck('name')->all(),
            'pgsql' => collect(DB::select(
                'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
                ['stories']
            ))->pluck('indexname')->all(),
            'mysql' => collect(DB::select(
                'select index_name from information_schema.statistics where table_schema = ? and table_name = ?',
                [DB::getDatabaseName(), 'stories']
            ))->pluck('index_name')->all(),
            default => null,
        };

        $portfolioPostIndexes = match ($driver) {
            'sqlite' => collect(DB::select("pragma index_list('portfolio_posts')"))->pluck('name')->all(),
            'pgsql' => collect(DB::select(
                'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
                ['portfolio_posts']
            ))->pluck('indexname')->all(),
            'mysql' => collect(DB::select(
                'select index_name from information_schema.statistics where table_schema = ? and table_name = ?',
                [DB::getDatabaseName(), 'portfolio_posts']
            ))->pluck('index_name')->all(),
            default => null,
        };

        if ($storiesIndexes === null || $portfolioPostIndexes === null) {
            $this->markTestSkipped("Unsupported driver for index assertions: {$driver}");
        }

        $this->assertContains('stories_bp_expires_at_index', $storiesIndexes);
        $this->assertContains('portfolio_posts_bp_published_at_index', $portfolioPostIndexes);
    }
}
