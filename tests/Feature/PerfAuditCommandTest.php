<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PerfAuditCommandTest extends TestCase
{
    public function test_perf_audit_can_filter_queries_by_only_option(): void
    {
        $exitCode = Artisan::call('perf:audit', ['--only' => 'catalog:newest']);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('catalog:newest', $output);
        $this->assertStringContainsString('from "offers"', $output);
        $this->assertStringNotContainsString('provider:offers', $output);
    }
}
