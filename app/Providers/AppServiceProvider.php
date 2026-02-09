<?php

namespace App\Providers;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Policies\BusinessProfilePolicy;
use App\Policies\OfferPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Policies (Laravel 11 default app skeleton doesn't include AuthServiceProvider)
        Gate::policy(BusinessProfile::class, BusinessProfilePolicy::class);
        Gate::policy(Offer::class, OfferPolicy::class);
    }
}
