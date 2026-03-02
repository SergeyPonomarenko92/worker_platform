<?php

use App\Models\Offer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('perf:audit {--explain : Run EXPLAIN for the sample queries (Postgres only)} {--analyze : Use EXPLAIN (ANALYZE, BUFFERS) (implies --explain)} {--provider=demo-provider : Provider slug for provider-show queries}', function () {
    $connection = DB::connection();
    $driver = (string) $connection->getDriverName();

    $providerSlug = (string) $this->option('provider');
    $explain = (bool) $this->option('explain') || (bool) $this->option('analyze');
    $analyze = (bool) $this->option('analyze');

    $this->line('---');
    $this->line('Perf audit helper');
    $this->line("DB driver: {$driver}");

    if ($explain && $driver !== 'pgsql') {
        $this->warn('EXPLAIN is only supported in this helper for Postgres (pgsql). Re-run without --explain to just print SQL.');
        $explain = false;
        $analyze = false;
    }

    $prefix = $analyze ? 'EXPLAIN (ANALYZE, BUFFERS) ' : 'EXPLAIN ';

    $queries = [
        'catalog:newest' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->latest('offers.created_at')
            ->limit(20),

        // Mirrors provider show offers block.
        'provider:offers' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active()->where('slug', $providerSlug))
            ->latest('offers.created_at')
            ->limit(12),

        // Portfolio posts (published) for provider show.
        'provider:portfolio_posts' => \App\Models\PortfolioPost::query()
            ->select('portfolio_posts.id')
            ->whereHas('businessProfile', fn ($bp) => $bp->active()->where('slug', $providerSlug))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit(6),

        // Stories (active) for provider show.
        'provider:stories' => \App\Models\Story::query()
            ->select('stories.id')
            ->whereHas('businessProfile', fn ($bp) => $bp->active()->where('slug', $providerSlug))
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->limit(6),
    ];

    foreach ($queries as $name => $query) {
        $this->line('---');
        $this->info($name);

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $this->line($sql);
        if (count($bindings)) {
            $this->line('Bindings: '.json_encode($bindings, JSON_UNESCAPED_UNICODE));
        }

        if (! $explain) {
            continue;
        }

        $rows = $connection->select($prefix.$sql, $bindings);
        foreach ($rows as $row) {
            // Postgres returns a single column named "QUERY PLAN".
            $line = (string) (array_values((array) $row)[0] ?? '');
            $this->line($line);
        }
    }

    $this->line('---');
    $this->comment('Tip: for more context see docs/perf-audit.md');
})->purpose('Print (and optionally EXPLAIN) sample queries for catalog/provider pages');
