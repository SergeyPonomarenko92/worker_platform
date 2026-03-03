<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Dashboard\BusinessProfileController;
use App\Http\Controllers\Dashboard\DealController;
use App\Http\Controllers\Dashboard\OfferController;
use App\Http\Controllers\Dashboard\PortfolioPostController;
use App\Http\Controllers\Dashboard\StoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ReviewController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('catalog.index', request()->query());
});

// Note: in production, `public/robots.txt` may be served directly by the web server.
// We still register a route so that it works in Laravel feature tests and in setups
// where the app handles all requests.
Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');

    $contents = file_exists($path)
        ? file_get_contents($path)
        : "User-agent: *\nDisallow:\n";

    // If we can serve /sitemap.xml, it's helpful to advertise it here.
    // Keep the static file as the main source of directives.
    if (! str_contains($contents, 'Sitemap:')) {
        $contents = rtrim($contents)."\n\n".'Sitemap: '.url('/sitemap.xml')."\n";
    }

    return response($contents, 200, ['Content-Type' => 'text/plain; charset=UTF-8'])
        // Safe caching: robots.txt changes rarely, but allow quick iteration.
        ->header('Cache-Control', 'max-age=300, public');
});

// Note: we serve sitemap via a route for the same reasons as robots.txt (tests + setups
// where the app handles all requests).
//
// IMPORTANT: sitemap <loc> values must be absolute URLs. Static files in public/
// cannot reliably embed APP_URL, so we generate the sitemap dynamically.
Route::get('/sitemap.xml', function () {
    $urls = [];

    $urls[] = [
        'loc' => url('/catalog'),
        'lastmod' => null,
    ];

    $providers = \App\Models\BusinessProfile::query()
        ->where('is_active', true)
        ->select(['slug', 'updated_at'])
        ->orderByDesc('updated_at')
        ->get();

    foreach ($providers as $provider) {
        $urls[] = [
            'loc' => url('/providers/'.$provider->slug),
            'lastmod' => optional($provider->updated_at)->toDateString(),
        ];
    }

    $escape = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

    foreach ($urls as $item) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>'.$escape($item['loc'])."</loc>\n";

        if (! empty($item['lastmod'])) {
            $xml .= '    <lastmod>'.$escape($item['lastmod'])."</lastmod>\n";
        }

        $xml .= "  </url>\n";
    }

    $xml .= "</urlset>\n";

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8'])
        // Safe caching: sitemap may update on provider changes; keep TTL modest.
        ->header('Cache-Control', 'max-age=300, public');
});

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/providers/{slug}', [ProviderController::class, 'show'])->name('providers.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Reviews (client flow)
    Route::get('/deals/{deal}/review/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/deals/{deal}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Provider cabinet (multiple business profiles)
    Route::get('/business-profiles', [BusinessProfileController::class, 'index'])->name('business-profiles.index');
    Route::get('/business-profiles/create', [BusinessProfileController::class, 'create'])->name('business-profiles.create');
    Route::post('/business-profiles', [BusinessProfileController::class, 'store'])->name('business-profiles.store');
    Route::get('/business-profiles/{businessProfile}/edit', [BusinessProfileController::class, 'edit'])->name('business-profiles.edit');
    Route::patch('/business-profiles/{businessProfile}', [BusinessProfileController::class, 'update'])->name('business-profiles.update');

    Route::scopeBindings()->group(function () {
        // Offers
        Route::get('/business-profiles/{businessProfile}/offers', [OfferController::class, 'index'])->name('offers.index');
        Route::get('/business-profiles/{businessProfile}/offers/create', [OfferController::class, 'create'])->name('offers.create');
        Route::post('/business-profiles/{businessProfile}/offers', [OfferController::class, 'store'])->name('offers.store');
        Route::get('/business-profiles/{businessProfile}/offers/{offer}/edit', [OfferController::class, 'edit'])->name('offers.edit');
        Route::patch('/business-profiles/{businessProfile}/offers/{offer}', [OfferController::class, 'update'])->name('offers.update');
        Route::delete('/business-profiles/{businessProfile}/offers/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');

        // Portfolio posts
        Route::get('/business-profiles/{businessProfile}/portfolio-posts', [PortfolioPostController::class, 'index'])->name('portfolio-posts.index');
        Route::get('/business-profiles/{businessProfile}/portfolio-posts/create', [PortfolioPostController::class, 'create'])->name('portfolio-posts.create');
        Route::post('/business-profiles/{businessProfile}/portfolio-posts', [PortfolioPostController::class, 'store'])->name('portfolio-posts.store');
        Route::get('/business-profiles/{businessProfile}/portfolio-posts/{portfolioPost}/edit', [PortfolioPostController::class, 'edit'])->name('portfolio-posts.edit');
        Route::patch('/business-profiles/{businessProfile}/portfolio-posts/{portfolioPost}', [PortfolioPostController::class, 'update'])->name('portfolio-posts.update');
        Route::delete('/business-profiles/{businessProfile}/portfolio-posts/{portfolioPost}', [PortfolioPostController::class, 'destroy'])->name('portfolio-posts.destroy');

        // Stories
        Route::get('/business-profiles/{businessProfile}/stories', [StoryController::class, 'index'])->name('stories.index');
        Route::get('/business-profiles/{businessProfile}/stories/create', [StoryController::class, 'create'])->name('stories.create');
        Route::post('/business-profiles/{businessProfile}/stories', [StoryController::class, 'store'])->name('stories.store');
        Route::get('/business-profiles/{businessProfile}/stories/{story}/edit', [StoryController::class, 'edit'])->name('stories.edit');
        Route::patch('/business-profiles/{businessProfile}/stories/{story}', [StoryController::class, 'update'])->name('stories.update');
        Route::delete('/business-profiles/{businessProfile}/stories/{story}', [StoryController::class, 'destroy'])->name('stories.destroy');

        // Deals
        Route::get('/business-profiles/{businessProfile}/deals', [DealController::class, 'index'])->name('deals.index');
        Route::get('/business-profiles/{businessProfile}/deals/create', [DealController::class, 'create'])->name('deals.create');
        Route::post('/business-profiles/{businessProfile}/deals', [DealController::class, 'store'])->name('deals.store');
        Route::get('/business-profiles/{businessProfile}/deals/{deal}', [DealController::class, 'show'])->name('deals.show');
        Route::patch('/business-profiles/{businessProfile}/deals/{deal}/in-progress', [DealController::class, 'markInProgress'])->name('deals.in-progress');
        Route::patch('/business-profiles/{businessProfile}/deals/{deal}/completed', [DealController::class, 'markCompleted'])->name('deals.completed');
        Route::patch('/business-profiles/{businessProfile}/deals/{deal}/cancelled', [DealController::class, 'markCancelled'])->name('deals.cancelled');
    });
});

require __DIR__.'/auth.php';
