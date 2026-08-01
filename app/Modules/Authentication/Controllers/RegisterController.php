<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Events\UserRegistered;
use App\Modules\Authentication\Requests\RegisterRequest;
use App\Modules\Authentication\Services\AuthService;
use App\Modules\Candidates\Models\CandidateProfile;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function show(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register($request->validated());

        // Create empty candidate profile linked to the user
        CandidateProfile::create([
            'user_id' => $user->id,
            'wilaya'  => null,
        ]);

        event(new UserRegistered($user));

        return redirect()->route('verification.notice')
            ->with('status', __('auth.registration_success'));
    }
}
