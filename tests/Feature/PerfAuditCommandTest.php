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
}
