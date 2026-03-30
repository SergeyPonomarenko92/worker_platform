<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessProfile;
use App\Support\QueryParamNormalizer;
use App\Support\SqlLikeEscaper;
use Illuminate\Http\Request;

class ProviderSuggestionsController
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

        $providers = BusinessProfile::query()
            ->select(['name', 'slug'])
            ->active()
            ->whereNotNull('slug')
            // Be defensive: legacy/manual data edits can leave empty/whitespace-only values.
            ->whereRaw("btrim(slug) <> ''")
            ->whereRaw("btrim(name) <> ''")
            // Suggest only providers that are actually visible in the catalog.
            ->whereHas('offers', fn ($offers) => $offers->active())
            ->where(function ($query) use ($qLike) {
                $query
                    ->whereRaw("lower(name) like ? escape '!'", ["%{$qLike}%"])
                    ->orWhereRaw("lower(slug) like ? escape '!'", ["%{$qLike}%"]);
            })
            ->orderByRaw('lower(name)')
            ->limit(10)
            ->get()
            ->map(fn (BusinessProfile $p) => [
                'name' => $p->name,
                'slug' => $p->slug,
            ])
            ->values();

        return response()->json($providers)
            ->header('Cache-Control', 'max-age=300, public');
    }
}
