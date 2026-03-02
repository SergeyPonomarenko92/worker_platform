<?php

use App\Models\Offer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('perf:audit {--explain : Run EXPLAIN for the sample queries (Postgres only)} {--analyze : Use EXPLAIN (ANALYZE, BUFFERS) (implies --explain)} {--provider=demo-provider : Provider slug for provider-show queries} {--city=ки : City prefix for the catalog:city_prefix query (case-insensitive)} {--price_from=100 : Min price bound for the catalog:price_range query} {--include_no_price=1 : Include offers with no price in the catalog:price_range query (0/1)}', function () {
    $connection = DB::connection();
    $driver = (string) $connection->getDriverName();

    $providerSlug = (string) $this->option('provider');
    $cityPrefix = (string) $this->option('city');
    $priceFrom = (int) $this->option('price_from');
    $includeNoPrice = (bool) ((int) $this->option('include_no_price'));

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

        // Mirrors the city prefix filter in /catalog (case-insensitive). Uses ESCAPE to align with the real UI behavior.
        'catalog:city_prefix' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', function ($bp) use ($cityPrefix) {
                $normalized = \App\Support\QueryParamNormalizer::text($cityPrefix);
                $escaped = \App\Support\SqlLikeEscaper::escape(mb_strtolower($normalized, 'UTF-8')).'%';

                $bp
                    ->active()
                    ->whereRaw("lower(city) LIKE ? ESCAPE '!'", [$escaped]);
            })
            ->latest('offers.created_at')
            ->limit(20),

        // Mirrors the price range filter (+ optional include-no-price) in /catalog.
        'catalog:price_range' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->where(function ($q) use ($priceFrom, $includeNoPrice) {
                $q->where(function ($q) use ($priceFrom) {
                    $q
                        ->whereNotNull('offers.price_from')
                        ->where('offers.price_from', '>=', $priceFrom);
                });

                if ($includeNoPrice) {
                    $q->orWhere(function ($q) {
                        $q
                            ->whereNull('offers.price_from')
                            ->whereNull('offers.price_to');
                    });
                }
            })
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
