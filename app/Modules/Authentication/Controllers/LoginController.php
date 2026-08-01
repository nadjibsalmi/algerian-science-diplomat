<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function show(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $user = $this->authService->attemptLogin(
            $request->only('email', 'password'),
            $request->boolean('remember'),
        );

        if ($user === null) {
            $request->hitRateLimiter();

            // Same generic message for both wrong email and wrong password
            // (prevents email enumeration — OWASP A07)
            return back()->withErrors(['email' => __('auth.failed')])->withInput($request->only('email'));
        }

        // Regenerate session immediately after successful authentication to prevent session fixation
        $request->session()->regenerate();

        $request->clearRateLimiter();

        // Log last login IP (for security alerts)
        $user->update([
            'last_login' => now(),
            'last_ip'    => $request->ip(),
        ]);

        // If 2FA is enabled, redirect to challenge page
        if ($user->two_factor_secret !== null) {
            // Log the user out from the guard but preserve a short-lived session key
            auth()->logout();
            session(['auth.2fa_user_id' => $user->id]);
            // Regenerate session after placing the 2FA token to avoid fixation
            $request->session()->regenerate();

            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended($this->redirectPath($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function redirectPath(\App\Models\User $user): string
    {
        if ($user->hasRole('Super Admin') || $user->hasRole('Platform Admin')) {
            return route('admin.dashboard');
        }

        if ($user->embassies()->exists()) {
            return route('embassy.dashboard');
        }

        return route('candidate.dashboard');
    }
}
