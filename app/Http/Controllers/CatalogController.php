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
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
        ]);

        $type = (string) ($data['type'] ?? '');
        $categoryId = $data['category_id'] ?? null;
        $city = preg_replace('/\s+/', ' ', trim((string) ($data['city'] ?? '')));
        $cityLower = mb_strtolower($city);
        $q = trim((string) ($data['q'] ?? ''));
        $priceFrom = $data['price_from'] ?? null;
        $priceTo = $data['price_to'] ?? null;
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
            // - if either bound is set: exclude offers with NULL price_from ("ціна за домовленістю")
            ->when(is_numeric($priceFrom) || is_numeric($priceTo), fn ($query) => $query->whereNotNull('price_from'))
            ->when(is_numeric($priceFrom), fn ($query) => $query->where('price_from', '>=', $priceFrom))
            ->when(is_numeric($priceTo), fn ($query) => $query->where('price_from', '<=', $priceTo));

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
                'sort' => $sort,
            ],
            'categories' => $categories,
            'offers' => $offers,
        ]);
    }
}
