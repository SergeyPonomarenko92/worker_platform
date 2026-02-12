<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function show(string $slug)
    {
        $provider = BusinessProfile::query()
            ->where('slug', $slug)
            ->with([
                'offers' => fn ($q) => $q->where('is_active', true)->latest()->limit(10),
                'portfolioPosts' => fn ($q) => $q
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->latest('published_at')
                    ->limit(12),
                'stories' => fn ($q) => $q->where('expires_at', '>', now())->latest()->limit(20),
                'reviews' => fn ($q) => $q->latest()->limit(20),
            ])
            ->firstOrFail();

        return Inertia::render('Providers/Show', [
            'provider' => $provider,
        ]);
    }
}
