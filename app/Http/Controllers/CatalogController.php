<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // Public page: be tolerant to malformed/invalid query params.
        // We'll keep only valid values and ignore the rest (no validation redirects).
        $validator = Validator::make($request->all(), [
            'type' => ['nullable', 'in:service,product'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => ['nullable', 'integer', 'min:0'],
            'include_no_price' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'max:32'],
            'provider' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $validator->fails()
            ? $validator->valid() // keep only valid values; ignore invalid (no redirects)
            : $validator->validated();

        $type = (string) ($data['type'] ?? '');
        $categoryId = $data['category_id'] ?? null;
        $categoryIds = null;
        if (is_numeric($categoryId)) {
            // Include child categories when a parent is selected.
            // Postgres recursive CTE keeps it simple and fast.
            $rows = DB::select(
                <<<SQL
                with recursive category_tree as (
                    select id from categories where id = ?
                    union all
                    select c.id from categories c
                    join category_tree ct on c.parent_id = ct.id
                )
                select id from category_tree
                SQL,
                [$categoryId]
            );

            $categoryIds = array_values(array_unique(array_map(fn ($r) => (int) $r->id, $rows)));
        }
        $city = \App\Support\QueryParamNormalizer::text((string) ($data['city'] ?? ''));
        $cityLower = mb_strtolower($city, 'UTF-8');
        // Escape user input for a LIKE prefix query.
        // Otherwise values like "100%" would be interpreted as wildcards.
        // We use `!` as an escape char (portable and avoids backslash edge-cases in SQL literals).
        $cityLike = \App\Support\SqlLikeEscaper::escape($cityLower);
        $q = \App\Support\QueryParamNormalizer::text((string) ($data['q'] ?? ''));
        // Escape user input for ILIKE queries so that characters like "%" and "_"
        // are treated literally (not as wildcards).
        // We use `!` as an escape char (same as city prefix filter).
        $qLike = \App\Support\SqlLikeEscaper::escape($q);

        $providerSlugLower = \App\Support\QueryParamNormalizer::providerSlug((string) ($data['provider'] ?? ''));
        $priceFrom = $data['price_from'] ?? null;
        $priceTo = $data['price_to'] ?? null;
        $includeNoPrice = (bool) ($data['include_no_price'] ?? false);
        $sort = (string) ($data['sort'] ?? 'newest');
        if (! in_array($sort, ['newest', 'price_asc', 'price_desc'], true)) {
            $sort = 'newest';
        }

        if (is_numeric($priceFrom) && is_numeric($priceTo) && $priceTo < $priceFrom) {
            [$priceFrom, $priceTo] = [$priceTo, $priceFrom];
        }

        // Only makes sense alongside a price range filter.
        if (! is_numeric($priceFrom) && ! is_numeric($priceTo)) {
            $includeNoPrice = false;
        }

        $offersQuery = Offer::query()
            ->select([
                'id',
                'business_profile_id',
                'category_id',
                'type',
                'title',
                'description',
                'price_from',
                'price_to',
                'currency',
                'created_at',
            ])
            ->with([
                'businessProfile:id,name,slug,city,is_active',
                'category:id,name',
            ])
            ->where('is_active', true)
            ->whereHas('businessProfile', fn ($bp) => $bp->where('is_active', true))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when(is_array($categoryIds) && count($categoryIds), fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->when($categoryId && (!is_array($categoryIds) || !count($categoryIds)), fn ($query) => $query->where('category_id', $categoryId))
            ->when($providerSlugLower, fn ($query) => $query->whereHas('businessProfile', fn ($bp) => $bp->where('slug', $providerSlugLower)))
            ->when($city, fn ($query) => $query->whereHas('businessProfile', fn ($bp) => $bp->whereRaw("lower(city) like ? escape '!'", ["{$cityLike}%"])))
            ->when($q, fn ($query) => $query->where(function ($sub) use ($qLike) {
                $pattern = "%{$qLike}%";

                $sub->whereRaw("title ilike ? escape '!'", [$pattern])
                    ->orWhereRaw("description ilike ? escape '!'", [$pattern])
                    ->orWhereHas('businessProfile', fn ($bp) => $bp->whereRaw("name ilike ? escape '!'", [$pattern]));
            }))
            // Price filter:
            // - if user sets price_from: show offers with known price_from >= price_from
            // - if user sets price_to: show offers with known price_from <= price_to
            // - by default, if any bound is set: exclude offers with NULL price_from ("ціна за домовленістю")
            // - if include_no_price=1: include NULL price offers alongside filtered priced offers
            ->when(is_numeric($priceFrom) || is_numeric($priceTo), function ($query) use ($priceFrom, $priceTo, $includeNoPrice) {
                if ($includeNoPrice) {
                    $query->where(function ($sub) use ($priceFrom, $priceTo) {
                        $sub->whereNull('price_from')
                            ->orWhere(function ($priced) use ($priceFrom, $priceTo) {
                                $priced->whereNotNull('price_from')
                                    ->when(is_numeric($priceFrom), fn ($q) => $q->where('price_from', '>=', $priceFrom))
                                    ->when(is_numeric($priceTo), fn ($q) => $q->where('price_from', '<=', $priceTo));
                            });
                    });

                    return;
                }

                $query
                    ->whereNotNull('price_from')
                    ->when(is_numeric($priceFrom), fn ($q) => $q->where('price_from', '>=', $priceFrom))
                    ->when(is_numeric($priceTo), fn ($q) => $q->where('price_from', '<=', $priceTo));
            });

        $offersQuery = match ($sort) {
            'price_asc' => $offersQuery->orderByRaw('price_from is null asc, price_from asc'),
            'price_desc' => $offersQuery->orderByRaw('price_from is null asc, price_from desc'),
            default => $offersQuery->latest(),
        };

        $offers = $offersQuery
            ->paginate(20)
            ->withQueryString();

        $categories = Category::query()
            ->with(['children' => fn ($q) => $q->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        return Inertia::render('Catalog/Index', [
            'filters' => [
                'type' => $type,
                'category_id' => $categoryId,
                'city' => $city,
                'q' => $q,
                'provider' => $providerSlugLower,
                'price_from' => $priceFrom,
                'price_to' => $priceTo,
                'include_no_price' => $includeNoPrice,
                'sort' => $sort,
            ],
            'categories' => $categories,
            'offers' => $offers,
        ]);
    }
}
