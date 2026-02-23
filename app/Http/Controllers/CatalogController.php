<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'type' => ['nullable', 'in:service,product'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => ['nullable', 'integer', 'min:0'],
            'include_no_price' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
        ]);

        $type = (string) ($data['type'] ?? '');
        $categoryId = $data['category_id'] ?? null;
        $city = preg_replace('/\s+/', ' ', trim((string) ($data['city'] ?? '')));
        $cityLower = mb_strtolower($city);
        $q = trim((string) ($data['q'] ?? ''));
        $priceFrom = $data['price_from'] ?? null;
        $priceTo = $data['price_to'] ?? null;
        $includeNoPrice = (bool) ($data['include_no_price'] ?? false);
        $sort = (string) ($data['sort'] ?? 'newest');

        if (is_numeric($priceFrom) && is_numeric($priceTo) && $priceTo < $priceFrom) {
            [$priceFrom, $priceTo] = [$priceTo, $priceFrom];
        }

        $offersQuery = Offer::query()
            ->with(['businessProfile', 'category'])
            ->where('is_active', true)
            ->whereHas('businessProfile', fn ($bp) => $bp->where('is_active', true))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($city, fn ($query) => $query->whereHas('businessProfile', fn ($bp) => $bp->whereRaw('lower(city) like ?', ["{$cityLower}%"])))
            ->when($q, fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('description', 'ilike', "%{$q}%");
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
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Catalog/Index', [
            'filters' => [
                'type' => $type,
                'category_id' => $categoryId,
                'city' => $city,
                'q' => $q,
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
