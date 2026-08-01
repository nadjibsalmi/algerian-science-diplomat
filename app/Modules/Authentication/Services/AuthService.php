<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'firstname'          => $data['firstname'],
            'lastname'           => $data['lastname'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'phone'              => $data['phone'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? 'fr',
            'status'             => 'pending',
        ]);
    }

    /**
     * Attempt to log in a user. Returns the User on success, null on failure.
     * Does NOT create a session — the caller handles session regeneration.
     */
    public function attemptLogin(array $credentials, bool $remember = false): ?User
    {
        if (! Auth::attempt($credentials, $remember)) {
            return null;
        }

        $user = Auth::user();

        if ($user->status === 'suspended') {
            Auth::logout();

            return null;
        }

        return $user;
    }
}
