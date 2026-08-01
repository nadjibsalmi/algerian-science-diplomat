<?php

use App\Modules\Offers\Controllers\OfferController;
use App\Modules\Offers\Controllers\OfferModerationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('manage/offers')->name('offers.manage.')->group(function (): void {
    Route::get('/', [OfferController::class, 'index'])->name('index');
    Route::post('/', [OfferController::class, 'store'])->name('store');
    Route::get('/moderation', [OfferModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderation/{offer}', [OfferModerationController::class, 'decide'])->name('moderation.decide');
    Route::get('/{offer}', [OfferController::class, 'show'])->name('show');
    Route::put('/{offer}', [OfferController::class, 'update'])->name('update');
    Route::delete('/{offer}', [OfferController::class, 'destroy'])->name('destroy');
    Route::post('/{offer}/submit', [OfferController::class, 'submit'])->name('submit');
    Route::post('/{offer}/publish', [OfferController::class, 'publish'])->name('publish');
    Route::post('/{offer}/pause', [OfferController::class, 'pause'])->name('pause');
    Route::post('/{offer}/close', [OfferController::class, 'close'])->name('close');
    Route::post('/{offer}/duplicate', [OfferController::class, 'duplicate'])->name('duplicate');
});