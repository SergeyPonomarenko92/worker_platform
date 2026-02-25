<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Deal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $now = now();

        $loadAllPortfolio = $request->boolean('all_portfolio');
        $loadAllReviews = $request->boolean('all_reviews');
        $loadAllOffers = $request->boolean('all_offers');

        $provider = BusinessProfile::query()
            ->select([
                'id',
                'user_id',
                'name',
                'slug',
                'about',
                'country_code',
                'city',
                'address',
                'phone',
                'website',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->withCount([
                'offers as offers_count' => fn ($q) => $q->where('is_active', true),
                'reviews as reviews_count',
                'portfolioPosts as published_portfolio_posts_count' => fn ($q) => $q
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', $now),
            ])
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->with([
                'offers' => fn ($q) => $q
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
                        'is_active',
                        'created_at',
                        'updated_at',
                    ])
                    ->with(['category:id,name'])
                    ->where('is_active', true)
                    ->latest()
                    ->when(! $loadAllOffers, fn ($q) => $q->limit(10))
                    ->when($loadAllOffers, fn ($q) => $q->limit(200)),
                'portfolioPosts' => fn ($q) => $q
                    ->select([
                        'id',
                        'business_profile_id',
                        'title',
                        'body',
                        'published_at',
                        'created_at',
                        'updated_at',
                    ])
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', $now)
                    ->latest('published_at')
                    // Preload a reasonable amount by default to keep page fast; UI shows only the first few anyway.
                    // Users can request the full list via ?all_portfolio=1.
                    ->when(! $loadAllPortfolio, fn ($q) => $q->limit(18))
                    ->when($loadAllPortfolio, fn ($q) => $q->limit(200)),
                'stories' => fn ($q) => $q
                    ->select([
                        'id',
                        'business_profile_id',
                        'caption',
                        'media_path',
                        'expires_at',
                        'created_at',
                        'updated_at',
                    ])
                    ->where('expires_at', '>', $now)
                    ->latest()
                    ->limit(20),
                'reviews' => fn ($q) => $q
                    ->select([
                        'id',
                        'deal_id',
                        'business_profile_id',
                        'client_user_id',
                        'rating',
                        'body',
                        'created_at',
                        'updated_at',
                    ])
                    ->with(['client:id,name'])
                    ->latest()
                    ->when(! $loadAllReviews, fn ($q) => $q->limit(20))
                    ->when($loadAllReviews, fn ($q) => $q->limit(200)),
            ])
            ->firstOrFail();

        $eligibleDealId = null;
        if (auth()->check()) {
            $eligibleDealId = Deal::query()
                ->where('business_profile_id', $provider->id)
                ->where('client_user_id', auth()->id())
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->where('completed_at', '<=', $now)
                ->whereDoesntHave('review')
                ->latest('completed_at')
                ->value('id');
        }

        return Inertia::render('Providers/Show', [
            'provider' => $provider,
            'eligibleDealId' => $eligibleDealId,
            'loadAllPortfolio' => $loadAllPortfolio,
            'loadAllReviews' => $loadAllReviews,
            'loadAllOffers' => $loadAllOffers,
        ]);
    }
}
