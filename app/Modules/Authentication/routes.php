<?php

use App\Modules\Authentication\Controllers\EmailVerificationController;
use App\Modules\Authentication\Controllers\LoginController;
use App\Modules\Authentication\Controllers\PasswordResetController;
use App\Modules\Authentication\Controllers\RegisterController;
use App\Modules\Authentication\Controllers\SessionController;
use App\Modules\Authentication\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

// ── Guest routes ────────────────────────────────────────────────────
Route::middleware('guest')->group(function (): void {
    Route::get('/inscription', [RegisterController::class, 'show'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store']);

    Route::get('/connexion', [LoginController::class, 'show'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:auth');

    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])->name('password.update');

    // 2FA challenge during login
    Route::get('/deux-facteurs/verification', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/deux-facteurs/verification', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
});

// ── Auth routes ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function (): void {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');

    // Email verification
    Route::get('/verification-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verification-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/verification-email/renvoyer', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 2FA setup (authenticated)
    Route::get('/deux-facteurs/configuration', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/deux-facteurs/activer', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/deux-facteurs/desactiver', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // Session management
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{sessionId}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('/sessions', [SessionController::class, 'destroyAll'])->name('sessions.destroy-all');
});
