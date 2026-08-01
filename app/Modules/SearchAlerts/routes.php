<?php

use App\Modules\SearchAlerts\Controllers\SearchAlertController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('search-alerts')->name('search-alerts.')->group(function (): void {
    Route::get('/', [SearchAlertController::class, 'index'])->name('index');
    Route::post('/', [SearchAlertController::class, 'store'])->name('store');
    Route::put('/{alert}', [SearchAlertController::class, 'update'])->name('update');
    Route::delete('/{alert}', [SearchAlertController::class, 'destroy'])->name('destroy');
});