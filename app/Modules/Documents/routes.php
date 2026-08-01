<?php

use App\Modules\Documents\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('documents')->name('documents.')->group(function (): void {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::post('/', [DocumentController::class, 'store'])->middleware('throttle:uploads')->name('store');
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
    Route::post('/{document}/share', [DocumentController::class, 'share'])->name('share');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
});

Route::get('/documents/shared/{token}', [DocumentController::class, 'shared'])
    ->middleware('throttle:uploads')
    ->name('documents.shared');