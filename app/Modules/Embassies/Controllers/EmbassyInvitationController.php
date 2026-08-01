<?php

namespace App\Modules\Embassies\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Embassies\Models\EmbassyInvitation;
use App\Modules\Embassies\Services\EmbassyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmbassyInvitationController extends Controller
{
    public function __construct(private readonly EmbassyService $service) {}

    public function show(string $token): JsonResponse
    {
        $invitation = EmbassyInvitation::query()
            ->with('embassy:id,official_name,country')
            ->where('token', hash('sha256', $token))
            ->firstOrFail();

        return response()->json([
            'invitation' => [
                'email' => $invitation->email,
                'role_in_embassy' => $invitation->role_in_embassy,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'accepted' => $invitation->accepted_at !== null,
                'embassy' => $invitation->embassy,
            ],
        ]);
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        $invitation = EmbassyInvitation::query()
            ->where('token', hash('sha256', $token))
            ->firstOrFail();

        $embassy = $this->service->acceptInvitation($invitation, $request->user());

        return response()->json([
            'message' => 'Invitation acceptée.',
            'embassy' => $embassy,
        ]);
    }
}