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
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
        ]);

        $type = (string) ($data['type'] ?? '');
        $categoryId = $data['category_id'] ?? null;
        $city = trim((string) ($data['city'] ?? ''));
        $q = trim((string) ($data['q'] ?? ''));
        $sort = (string) ($data['sort'] ?? 'newest');

        $offersQuery = Offer::query()
            ->with(['businessProfile', 'category'])
            ->where('is_active', true)
            ->whereHas('businessProfile', fn ($bp) => $bp->where('is_active', true))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($city, fn ($query) => $query->whereHas('businessProfile', fn ($bp) => $bp->where('city', 'ilike', "%{$city}%")))
            ->when($q, fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('description', 'ilike', "%{$q}%");
            }));

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
                'sort' => $sort,
            ],
            'categories' => $categories,
            'offers' => $offers,
        ]);
    }
}
