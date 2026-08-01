<?php

use App\Modules\Notifications\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function (): void {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read', [NotificationController::class, 'markRead'])->name('read.all');
    Route::get('/preferences', [NotificationController::class, 'preferences'])->name('preferences');
    Route::put('/preferences', [NotificationController::class, 'updatePreference'])->name('preferences.update');
    Route::get('/alerts', [NotificationController::class, 'alerts'])->name('alerts.index');
    Route::post('/alerts', [NotificationController::class, 'storeAlert'])->name('alerts.store');
    Route::put('/alerts/{alert}', [NotificationController::class, 'updateAlert'])->name('alerts.update');
    Route::delete('/alerts/{alert}', [NotificationController::class, 'destroyAlert'])->name('alerts.destroy');
    Route::get('/digest', [NotificationController::class, 'digest'])->name('digest');
    Route::put('/digest', [NotificationController::class, 'updateDigest'])->name('digest.update');
    Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read.one');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});