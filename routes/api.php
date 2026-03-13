<?php

use App\Http\Controllers\Api\CitySuggestionsController;
use Illuminate\Support\Facades\Route;

Route::get('/cities', CitySuggestionsController::class)
    ->name('api.cities');
