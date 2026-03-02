<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PerfAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_perf_audit_command_runs_without_explain(): void
    {
        $exitCode = Artisan::call('perf:audit', [
            '--only' => 'catalog:newest',
        ]);

        $this->assertSame(0, $exitCode);

        $output = Artisan::output();

        $this->assertStringContainsString('Perf audit helper', $output);
        $this->assertStringContainsString('catalog:newest', $output);
        $this->assertStringContainsString('Bindings:', $output);
    }
}
