<?php

use App\Modules\Search\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/search/offers', [SearchController::class, 'offers'])
    ->middleware('throttle:search')
    ->name('search.offers');