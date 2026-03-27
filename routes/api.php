<?php

use App\Http\Controllers\Api\CategorySuggestionsController;
use App\Http\Controllers\Api\CitySuggestionsController;
use App\Http\Controllers\Api\ProviderSuggestionsController;
use Illuminate\Support\Facades\Route;

Route::get('/cities', CitySuggestionsController::class)
    ->name('api.cities');

Route::get('/categories', CategorySuggestionsController::class)
    ->name('api.categories');

Route::get('/providers', ProviderSuggestionsController::class)
    ->name('api.providers');
