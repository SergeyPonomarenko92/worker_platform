<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Deal;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function show(string $slug)
    {
        $provider = BusinessProfile::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->withCount([
                'offers as offers_count' => fn ($q) => $q->where('is_active', true),
                'reviews as reviews_count',
                'portfolioPosts as published_portfolio_posts_count' => fn ($q) => $q
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now()),
            ])
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->with([
                'offers' => fn ($q) => $q
                    ->with(['category:id,name'])
                    ->where('is_active', true)
                    ->latest()
                    ->limit(10),
                'portfolioPosts' => fn ($q) => $q
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->latest('published_at')
                    ->limit(60),
                'stories' => fn ($q) => $q->where('expires_at', '>', now())->latest()->limit(20),
                'reviews' => fn ($q) => $q->with(['client:id,name'])->latest()->limit(20),
            ])
            ->firstOrFail();

        $eligibleDealId = null;
        if (auth()->check()) {
            $eligibleDealId = Deal::query()
                ->where('business_profile_id', $provider->id)
                ->where('client_user_id', auth()->id())
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->latest('completed_at')
                ->value('id');
        }

        return Inertia::render('Providers/Show', [
            'provider' => $provider,
            'eligibleDealId' => $eligibleDealId,
        ]);
    }
}
