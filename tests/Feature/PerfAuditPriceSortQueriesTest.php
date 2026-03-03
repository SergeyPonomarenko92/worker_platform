<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PerfAuditPriceSortQueriesTest extends TestCase
{
    public function test_perf_audit_can_render_catalog_price_sort_queries(): void
    {
        $exitCode = Artisan::call('perf:audit', [
            '--only' => 'catalog:price_asc,catalog:price_desc',
        ]);

        $this->assertSame(0, $exitCode);

        $output = Artisan::output();

        $this->assertStringContainsString('catalog:price_asc', $output);
        $this->assertStringContainsString('catalog:price_desc', $output);

        // Ensure ordering expression is present (mirrors CatalogController sort behavior).
        $this->assertStringContainsString('price_from is null asc', strtolower($output));
    }
}
