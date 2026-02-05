<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->string('type')->toString(); // service|product
        $q = $request->string('q')->toString();

        $offers = Offer::query()
            ->with(['businessProfile', 'category'])
            ->where('is_active', true)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($q, fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Catalog/Index', [
            'filters' => [
                'type' => $type,
                'q' => $q,
            ],
            'offers' => $offers,
        ]);
    }
}
