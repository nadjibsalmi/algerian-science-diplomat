<?php

use App\Modules\Analytics\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::post('/analytics/events', [AnalyticsController::class, 'record'])->middleware('throttle:search')->name('analytics.events');
Route::get('/admin/analytics', [AnalyticsController::class, 'dashboard'])->middleware('auth')->name('admin.analytics');