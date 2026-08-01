<?php

use App\Modules\Candidates\Controllers\CandidateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('candidate')->name('candidate.')->group(function (): void {
    Route::get('/dashboard', [CandidateController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [CandidateController::class, 'profile'])->name('profile');
    Route::put('/profile', [CandidateController::class, 'updateProfile'])->name('profile.update');
    Route::get('/favorites', [CandidateController::class, 'favorites'])->name('favorites.index');
    Route::post('/favorites/toggle', [CandidateController::class, 'toggleFavorite'])->name('favorites.toggle');

    Route::get('/{section}', [CandidateController::class, 'entries'])->name('entries.index');
    Route::post('/{section}', [CandidateController::class, 'storeEntry'])->name('entries.store');
    Route::put('/{section}/{entry}', [CandidateController::class, 'updateEntry'])->name('entries.update');
    Route::delete('/{section}/{entry}', [CandidateController::class, 'deleteEntry'])->name('entries.delete');

});