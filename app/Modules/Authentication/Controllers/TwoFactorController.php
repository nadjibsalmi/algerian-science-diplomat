<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Authentication\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactorService) {}

    /** Show the 2FA challenge page during login */
    public function challenge(): Response
    {
        abort_unless(session()->has('auth.2fa_user_id'), 403);

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    /** Verify the TOTP code submitted during login */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $userId = session('auth.2fa_user_id');
        abort_unless($userId !== null, 403);

        $user = User::findOrFail($userId);

        if (! $this->twoFactorService->verify($user, $request->code)) {
            return back()->withErrors(['code' => __('auth.two_factor_failed')]);
        }

        session()->forget('auth.2fa_user_id');
        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('candidate.dashboard'));
    }

    /** Show the 2FA setup page (authenticated user) */
    public function setup(Request $request): Response
    {
        $user = $request->user();
        $secret = $this->twoFactorService->generateSecret();

        session(['2fa.setup_secret' => $secret]);

        return Inertia::render('Auth/TwoFactorSetup', [
            'qrCode'     => $this->twoFactorService->getQrCodeUrl($user, $secret),
            'secret'     => $secret,
            'enabled'    => $user->two_factor_secret !== null,
            'recoveryCodes' => $user->two_factor_recovery_codes
                ? json_decode(decrypt($user->two_factor_recovery_codes), true)
                : [],
        ]);
    }

    /** Enable 2FA after verifying the first code */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'digits:6']]);

        $secret = session('2fa.setup_secret');
        abort_unless($secret !== null, 403);

        $user = $request->user();

        if (! $this->twoFactorService->verifyRaw($user, $secret, $request->code)) {
            return back()->withErrors(['code' => __('auth.two_factor_failed')]);
        }

        $codes = $this->twoFactorService->generateRecoveryCodes();

        $user->update([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            '2fa_enabled'               => true,
        ]);

        session()->forget('2fa.setup_secret');
        activity()->causedBy($user)->log('2FA enabled');

        return redirect()->route('candidate.settings')
            ->with('2fa_codes', $codes);
    }

    /** Disable 2FA (requires password confirmation) */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            '2fa_enabled'               => false,
        ]);

        activity()->causedBy($user)->log('2FA disabled');

        return redirect()->route('candidate.settings')
            ->with('status', __('auth.two_factor_disabled'));
    }
}
