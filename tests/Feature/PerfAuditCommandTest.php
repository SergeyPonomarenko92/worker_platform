<?php

namespace Tests\Feature;

use Tests\TestCase;

class PerfAuditCommandTest extends TestCase
{
    public function test_perf_audit_explain_is_gracefully_disabled_on_non_pgsql(): void
    {
        // In the test environment we typically use sqlite. The command should not crash
        // when --explain is requested; it should still print SQL output.
        $this->artisan('perf:audit --explain')
            ->expectsOutputToContain('Perf audit helper')
            ->expectsOutputToContain('DB driver:')
            ->expectsOutputToContain('catalog:newest')
            ->assertSuccessful();
    }

    public function test_perf_audit_only_filter_can_limit_output_to_catalog_group(): void
    {
        $this->artisan('perf:audit --only=catalog')
            ->expectsOutputToContain('catalog:newest')
            ->expectsOutputToContain('catalog:city_prefix')
            ->expectsOutputToContain('catalog:price_range')
            ->doesntExpectOutputToContain('provider:offers')
            ->doesntExpectOutputToContain('provider:portfolio_posts')
            ->doesntExpectOutputToContain('provider:stories')
            ->assertSuccessful();
    }

    public function test_perf_audit_only_filter_can_limit_output_to_specific_query(): void
    {
        $this->artisan('perf:audit --only=provider:offers')
            ->expectsOutputToContain('provider:offers')
            ->doesntExpectOutputToContain('catalog:newest')
            ->doesntExpectOutputToContain('provider:portfolio_posts')
            ->doesntExpectOutputToContain('provider:stories')
            ->assertSuccessful();
    }

    public function test_perf_audit_only_filter_accepts_multiple_queries(): void
    {
        $this->artisan('perf:audit --only=catalog:newest,provider:offers')
            ->expectsOutputToContain('catalog:newest')
            ->expectsOutputToContain('provider:offers')
            ->doesntExpectOutputToContain('catalog:city_prefix')
            ->doesntExpectOutputToContain('provider:stories')
            ->assertSuccessful();
    }
}
