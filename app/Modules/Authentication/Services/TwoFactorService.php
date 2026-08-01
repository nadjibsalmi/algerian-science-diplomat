<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

class TwoFactorService
{
    private const ISSUER = 'ASD Platform';

    public function generateSecret(): string
    {
        return trim(Base32::encodeUpper(random_bytes(20)), '=');
    }

    public function getQrCodeUrl(User $user, string $secret): string
    {
        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($user->email);
        $totp->setIssuer(self::ISSUER);

        return $totp->getProvisioningUri();
    }

    /** Verify a TOTP code against a user's stored (encrypted) secret */
    public function verify(User $user, string $code): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);

        return $this->verifyRaw($user, $secret, $code);
    }

    /** Verify a TOTP code against a raw (unencrypted) secret */
    public function verifyRaw(User $user, string $secret, string $code): bool
    {
        $totp = TOTP::createFromSecret($secret);
        $window = (int) config('auth.two_factor.window', 1);

        return $totp->verify($code, null, $window);
    }

    /** Generate 8 one-time recovery codes */
    public function generateRecoveryCodes(): array
    {
        return array_map(
            fn () => strtoupper(bin2hex(random_bytes(5))) . '-' . strtoupper(bin2hex(random_bytes(5))),
            range(1, 8),
        );
    }

    /** Check and consume a recovery code */
    public function useRecoveryCode(User $user, string $code): bool
    {
        if ($user->two_factor_recovery_codes === null) {
            return false;
        }

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $normalized = strtoupper(trim($code));

        if (! in_array($normalized, $codes, true)) {
            return false;
        }

        // Consume the used code
        $remaining = array_values(array_filter($codes, fn ($c) => $c !== $normalized));
        $user->update(['two_factor_recovery_codes' => encrypt(json_encode($remaining))]);

        return true;
    }
}
