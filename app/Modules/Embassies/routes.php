<?php

use App\Modules\Embassies\Controllers\EmbassyController;
use App\Modules\Embassies\Controllers\EmbassyInvitationController;
use App\Modules\Embassies\Controllers\EmbassyMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('embassy')->name('embassy.')->group(function (): void {
    Route::get('/invitations/{token}', [EmbassyInvitationController::class, 'show'])
        ->name('invitations.show');

    Route::middleware('auth')->group(function (): void {
        Route::get('/dashboard', [EmbassyController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [EmbassyController::class, 'show'])->name('profile.show');
        Route::put('/profile', [EmbassyController::class, 'update'])->name('profile.update');
        Route::get('/members', [EmbassyMemberController::class, 'index'])->name('members.index');
        Route::post('/members/invitations', [EmbassyMemberController::class, 'invite'])
            ->middleware('throttle:auth')
            ->name('members.invite');
        Route::get('/members/invitations', [EmbassyMemberController::class, 'invitations'])
            ->name('members.invitations');
        Route::delete('/members/invitations/{invitation}', [EmbassyMemberController::class, 'revokeInvitation'])
            ->name('members.invitations.revoke');
        Route::patch('/members/{member}', [EmbassyMemberController::class, 'update'])
            ->name('members.update');
        Route::delete('/members/{member}', [EmbassyMemberController::class, 'destroy'])
            ->name('members.destroy');
        Route::post('/invitations/{token}/accept', [EmbassyInvitationController::class, 'accept'])
            ->name('invitations.accept');
    });
});