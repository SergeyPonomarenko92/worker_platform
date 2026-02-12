<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Dashboard\BusinessProfileController;
use App\Http\Controllers\Dashboard\OfferController;
use App\Http\Controllers\Dashboard\PortfolioPostController;
use App\Http\Controllers\Dashboard\StoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
    });
});

require __DIR__.'/auth.php';
