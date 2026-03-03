<?php

use App\Models\Offer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('perf:audit {--list : List available sample queries and exit} {--json : Output JSON (useful for tooling/CI)} {--explain : Run EXPLAIN for the sample queries (Postgres only)} {--analyze : Use EXPLAIN (ANALYZE, BUFFERS) (implies --explain)} {--only= : Filter queries by group (catalog|provider) or by name (comma-separated, e.g. catalog:newest,provider:offers)} {--provider=demo-provider : Provider slug for provider-show queries} {--client=1 : Client user id for provider:eligible_deal query} {--category_id=1 : Category id for the catalog:category_tree query} {--city=ки : City prefix for the catalog:city_prefix query (case-insensitive)} {--q=майстер : Free-text search for the catalog:q_search query (escaped for ILIKE)} {--price_from=100 : Min price bound for the catalog:price_range query} {--price_to= : Max price bound for the catalog:price_range query} {--include_no_price=1 : Include offers with no price in the catalog:price_range query (0/1)} {--limit= : Override LIMIT for list-style queries (keeps provider:eligible_deal at 1)}', function () {
    $connection = DB::connection();
    $driver = (string) $connection->getDriverName();

    $list = (bool) $this->option('list');
    $json = (bool) $this->option('json');

    $providerSlug = \App\Support\QueryParamNormalizer::providerSlug((string) $this->option('provider'));
    $clientUserId = (int) $this->option('client');
    $categoryId = (int) $this->option('category_id');
    $cityPrefix = \App\Support\QueryParamNormalizer::text((string) $this->option('city'));
    $q = \App\Support\QueryParamNormalizer::text((string) $this->option('q'));

    $priceFrom = $this->option('price_from');
    $priceFrom = $priceFrom === null ? null : (int) $priceFrom;

    $priceTo = $this->option('price_to');
    $priceTo = $priceTo === null || $priceTo === '' ? null : (int) $priceTo;

    $includeNoPrice = (bool) ((int) $this->option('include_no_price'));

    $limitOverride = $this->option('limit');
    $limitOverride = $limitOverride === null ? null : (int) $limitOverride;

    $only = (string) ($this->option('only') ?? '');

    $explain = (bool) $this->option('explain') || (bool) $this->option('analyze');
    $analyze = (bool) $this->option('analyze');

    $meta = [
        'tool' => 'perf:audit',
        'db_driver' => $driver,
        'params' => [
            'provider_slug' => $providerSlug,
            'client_user_id' => $clientUserId,
            'category_id' => $categoryId,
            'city_prefix' => $cityPrefix,
            'q' => $q,
            'price_from' => $priceFrom,
            'price_to' => $priceTo,
            'include_no_price' => $includeNoPrice ? 1 : 0,
            'limit_override' => $limitOverride,
        ],
        'flags' => [
            'list' => $list,
            'json' => $json,
            'explain_requested' => $explain,
            'analyze_requested' => $analyze,
        ],
        'warnings' => [],
    ];

    if (! $json) {
        $this->line('---');
        $this->line('Perf audit helper');
        $this->line("DB driver: {$driver}");

        $this->line('Effective params:');
        $this->line("- provider_slug: {$providerSlug}");
        $this->line("- client_user_id: {$clientUserId}");
        $this->line("- category_id: {$categoryId}");
        $this->line("- city_prefix: {$cityPrefix}");
        $this->line("- q: {$q}");
        $this->line('- price_from: '.($priceFrom === null ? 'null' : (string) $priceFrom));
        $this->line('- price_to: '.($priceTo === null ? 'null' : (string) $priceTo));
        $this->line('- include_no_price: '.($includeNoPrice ? '1' : '0'));
        $this->line('- limit_override: '.($limitOverride === null ? 'null' : (string) $limitOverride));
    }

    if ($explain && $driver !== 'pgsql') {
        $meta['warnings'][] = 'EXPLAIN is only supported in this helper for Postgres (pgsql). Re-run without --explain to just print SQL.';

        if (! $json) {
            $this->warn($meta['warnings'][array_key_last($meta['warnings'])]);
        }

        $explain = false;
        $analyze = false;
    }

    $meta['flags']['explain_enabled'] = $explain;
    $meta['flags']['analyze_enabled'] = $analyze;

    $prefix = $analyze ? 'EXPLAIN (ANALYZE, BUFFERS) ' : 'EXPLAIN ';

    $allQueries = [
        // Recursive category tree CTE used by /catalog category filter.
        // Useful for EXPLAIN-ing index usage on categories.parent_id.
        'catalog:category_tree' => DB::query()
            ->select('id')
            ->fromRaw(
                <<<SQL
                (
                    with recursive category_tree as (
                        select id from categories where id = ?
                        union all
                        select c.id from categories c
                        join category_tree ct on c.parent_id = ct.id
                    )
                    select id from category_tree
                ) as category_tree
                SQL,
                [$categoryId]
            ),

        // Mirrors the category_id filter in /catalog including descendant categories.
        // Useful for EXPLAIN-ing the full query shape (CTE + offers join + ordering).
        'catalog:category_filter' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->whereIn('offers.category_id', DB::query()
                ->select('id')
                ->fromRaw(
                    <<<SQL
                    (
                        with recursive category_tree as (
                            select id from categories where id = ?
                            union all
                            select c.id from categories c
                            join category_tree ct on c.parent_id = ct.id
                        )
                        select id from category_tree
                    ) as category_tree
                    SQL,
                    [$categoryId]
                )
            )
            ->latest('offers.created_at')
            ->limit(20),

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
                $escaped = \App\Support\SqlLikeEscaper::escape(mb_strtolower($cityPrefix, 'UTF-8')).'%';

                $bp
                    ->active()
                    ->whereRaw("lower(city) LIKE ? ESCAPE '!'", [$escaped]);
            })
            ->latest('offers.created_at')
            ->limit(20),

        // Mirrors the free-text search filter (q) in /catalog.
        // Useful for checking ILIKE performance/indexes and ensuring the escape strategy stays consistent.
        'catalog:q_search' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->where(function ($sub) use ($q) {
                $escaped = \App\Support\SqlLikeEscaper::escape($q);
                $pattern = "%{$escaped}%";

                $sub->whereRaw("title ilike ? escape '!'", [$pattern])
                    ->orWhereRaw("description ilike ? escape '!'", [$pattern])
                    ->orWhereHas('businessProfile', fn ($bp) => $bp->whereRaw("name ilike ? escape '!'", [$pattern]));
            })
            ->latest('offers.created_at')
            ->limit(20),

        // Mirrors the price range filter (+ optional include-no-price) in /catalog.
        'catalog:price_range' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->where(function ($q) use ($priceFrom, $priceTo, $includeNoPrice) {
                $q->where(function ($q) use ($priceFrom, $priceTo) {
                    $q
                        ->whereNotNull('offers.price_from')
                        ->when(is_numeric($priceFrom), fn ($q) => $q->where('offers.price_from', '>=', $priceFrom))
                        ->when(is_numeric($priceTo), fn ($q) => $q->where('offers.price_from', '<=', $priceTo));
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

        // Mirrors the provider filter in /catalog (by business_profiles.slug).
        'catalog:provider_slug' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active()->where('slug', $providerSlug))
            ->latest('offers.created_at')
            ->limit(20),

        // Mirrors the sort=price_asc option in /catalog (NULL prices last).
        'catalog:price_asc' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->orderByRaw('price_from is null asc, price_from asc')
            ->limit(20),

        // Mirrors the sort=price_desc option in /catalog (NULL prices last).
        'catalog:price_desc' => Offer::query()
            ->select('offers.id')
            ->active()
            ->whereHas('businessProfile', fn ($bp) => $bp->active())
            ->orderByRaw('price_from is null asc, price_from desc')
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

        // Reviews list for provider show (latest reviews block).
        'provider:reviews' => \App\Models\Review::query()
            ->select('reviews.id')
            ->whereHas('businessProfile', fn ($bp) => $bp->active()->where('slug', $providerSlug))
            ->latest('reviews.created_at')
            ->limit(20),

        // Mirrors ProviderController@show eligibleDealId lookup.
        // Useful to sanity-check indexes for the "leave a review" CTA.
        'provider:eligible_deal' => \App\Models\Deal::query()
            ->select('deals.id')
            ->whereHas('businessProfile', fn ($bp) => $bp->active()->where('slug', $providerSlug))
            ->where('deals.client_user_id', $clientUserId)
            ->where('deals.status', 'completed')
            ->whereNotNull('deals.completed_at')
            ->where('deals.completed_at', '<=', now())
            ->whereDoesntHave('review')
            ->latest('deals.completed_at')
            ->limit(1),
    ];

    if ($limitOverride !== null && $limitOverride > 0) {
        foreach ($allQueries as $name => $query) {
            if ($name === 'provider:eligible_deal') {
                continue;
            }

            $allQueries[$name] = $query->limit($limitOverride);
        }
    }

    if ($list) {
        if ($json) {
            $payload = $meta + [
                'queries' => array_keys($allQueries),
            ];

            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return 0;
        }

        $this->line('Available queries:');

        foreach (array_keys($allQueries) as $name) {
            $this->line("- {$name}");
        }

        $this->line('---');
        $this->comment('Tip: for more context see docs/perf-audit.md');

        return 0;
    }

    $queries = $allQueries;

    if ($only !== '') {
        $filters = array_values(array_filter(array_map(static fn ($v) => trim((string) $v), explode(',', $only))));

        $queries = array_filter(
            $allQueries,
            static function ($query, string $name) use ($filters) {
                foreach ($filters as $filter) {
                    if ($filter === '') {
                        continue;
                    }

                    if ($filter === $name) {
                        return true;
                    }

                    if (str_ends_with($filter, ':') && str_starts_with($name, $filter)) {
                        return true;
                    }

                    if ($filter === 'catalog' && str_starts_with($name, 'catalog:')) {
                        return true;
                    }

                    if ($filter === 'provider' && str_starts_with($name, 'provider:')) {
                        return true;
                    }
                }

                return false;
            },
            ARRAY_FILTER_USE_BOTH,
        );
    }

    if (count($queries) === 0) {
        if ($json) {
            $payload = $meta + [
                'error' => 'No queries matched the provided --only filter.',
                'queries' => [],
                'available_queries' => array_keys($allQueries),
            ];

            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return 1;
        }

        $this->warn('No queries matched the provided --only filter.');
        $this->line('Available queries:');

        foreach (array_keys($allQueries) as $name) {
            $this->line("- {$name}");
        }

        return 1;
    }

    if ($json) {
        $results = [];

        foreach ($queries as $name => $query) {
            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $result = [
                'name' => $name,
                'sql' => $sql,
                'bindings' => $bindings,
            ];

            if ($explain) {
                $rows = $connection->select($prefix.$sql, $bindings);
                $result['explain'] = array_map(
                    static function ($row) {
                        // Postgres returns a single column named "QUERY PLAN".
                        return (string) (array_values((array) $row)[0] ?? '');
                    },
                    $rows,
                );
            }

            $results[] = $result;
        }

        $payload = $meta + [
            'queries' => $results,
        ];

        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return 0;
    }

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
