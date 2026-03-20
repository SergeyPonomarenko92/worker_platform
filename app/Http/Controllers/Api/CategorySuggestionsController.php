<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Support\QueryParamNormalizer;
use App\Support\SqlLikeEscaper;
use Illuminate\Http\Request;

class CategorySuggestionsController
{
    public function __invoke(Request $request)
    {
        $q = QueryParamNormalizer::text($request->query('q'));

        if (mb_strlen($q, 'UTF-8') < 2) {
            return response()->json([]);
        }

        $qLower = mb_strtolower($q, 'UTF-8');
        $qLike = SqlLikeEscaper::escape($qLower);

        $categories = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug'])
            // Suggest only categories that are actually visible in the catalog.
            ->whereHas('offers', fn ($offers) => $offers->active())
            ->whereRaw("lower(name) like ? escape '!'", ["%{$qLike}%"])
            ->with('parent.parent.parent.parent.parent')
            // Ensure stable case-insensitive ordering.
            ->orderByRaw('lower(name)')
            ->limit(10)
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'path' => self::path($category),
                ];
            })
            ->values();

        return response()->json($categories);
    }

    private static function path(Category $category): string
    {
        $names = [$category->name];

        $current = $category;
        $guard = 0;

        while ($current->parent && $guard < 10) {
            $current = $current->parent;
            array_unshift($names, $current->name);
            $guard++;
        }

        return implode(' → ', $names);
    }
}
