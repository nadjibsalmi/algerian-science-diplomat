<?php

use App\Modules\Applications\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('applications')->name('candidate.applications.')->group(function (): void {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::post('/offers/{offer}', [ApplicationController::class, 'store'])->name('store');
    Route::get('/{application}', [ApplicationController::class, 'show'])->name('show');
    Route::post('/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('withdraw');
    Route::post('/{application}/documents', [ApplicationController::class, 'attachDocuments'])->name('documents.attach');
});

Route::middleware('auth')->prefix('embassies/offers')->name('embassy.applications.')->group(function (): void {
    Route::get('/{offer}/applications', [ApplicationController::class, 'offerApplications'])->name('index');
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('status');
});