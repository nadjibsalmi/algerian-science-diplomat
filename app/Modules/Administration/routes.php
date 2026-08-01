<?php

use App\Modules\Administration\Controllers\AdministrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/overview', [AdministrationController::class, 'overview'])->name('overview');
    Route::get('/users', [AdministrationController::class, 'users'])->name('users');
    Route::post('/users/{user}/suspend', [AdministrationController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/lift-suspension', [AdministrationController::class, 'lift'])->name('users.lift');
    Route::get('/settings', [AdministrationController::class, 'settings'])->name('settings');
    Route::put('/settings/{key}', [AdministrationController::class, 'putSetting'])->name('settings.update');
    Route::get('/audit-logs', [AdministrationController::class, 'auditLogs'])->name('audit-logs');
});