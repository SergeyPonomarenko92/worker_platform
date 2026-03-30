<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessProfile;
use App\Support\QueryParamNormalizer;
use App\Support\SqlLikeEscaper;
use Illuminate\Http\Request;

class CitySuggestionsController
{
    public function __invoke(Request $request)
    {
        $q = QueryParamNormalizer::text($request->query('q'));

        if (mb_strlen($q, 'UTF-8') < 2) {
            return response()->json([])
                ->header('Cache-Control', 'max-age=300, public');
        }

        $qLower = mb_strtolower($q, 'UTF-8');
        $qLike = SqlLikeEscaper::escape($qLower);

        $cities = BusinessProfile::query()
            ->select(['city'])
            ->active()
            ->whereNotNull('city')
            // Be defensive: legacy/manual data edits can leave empty/whitespace-only cities in DB.
            ->whereRaw("btrim(city) <> ''")
            // Suggest only cities that are actually visible in the catalog.
            ->whereHas('offers', fn ($offers) => $offers->active())
            ->whereRaw("lower(city) like ? escape '!'", ["{$qLike}%"])
            // Use GROUP BY for a stable distinct list while allowing case-insensitive ordering.
            ->groupBy('city')
            ->orderByRaw('lower(city)')
            ->limit(10)
            ->pluck('city')
            ->values();

        return response()->json($cities)
            ->header('Cache-Control', 'max-age=300, public');
    }
}
