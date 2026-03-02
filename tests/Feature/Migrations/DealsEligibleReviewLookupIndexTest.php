<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DealsEligibleReviewLookupIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_deals_has_index_for_eligible_review_lookup_query(): void
    {
        $driver = DB::getDriverName();

        $indexes = match ($driver) {
            'sqlite' => collect(DB::select("pragma index_list('deals')"))->pluck('name')->all(),
            'pgsql' => collect(DB::select(
                'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
                ['deals']
            ))->pluck('indexname')->all(),
            'mysql' => collect(DB::select(
                'select index_name from information_schema.statistics where table_schema = ? and table_name = ?',
                [DB::getDatabaseName(), 'deals']
            ))->pluck('index_name')->all(),
            default => null,
        };

        if ($indexes === null) {
            $this->markTestSkipped("Unsupported driver for index assertions: {$driver}");
        }

        // We keep a single index that supports ProviderController eligible-deal lookup:
        // (business_profile_id, client_user_id, status, completed_at)
        // Historical migrations used a few different explicit names. Accept any of the known ones.
        $this->assertTrue(
            in_array('deals_bp_client_status_completed_at_idx', $indexes, true)
                || in_array('deals_bp_client_status_completed_at_index', $indexes, true),
            'Expected deals eligible lookup composite index to exist (either deals_bp_client_status_completed_at_idx or deals_bp_client_status_completed_at_index).'
        );
    }
}
