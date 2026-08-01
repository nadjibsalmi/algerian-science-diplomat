<?php

namespace App\Modules\Embassies\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Embassies\Requests\InviteMemberRequest;
use App\Modules\Embassies\Requests\UpdateMemberRequest;
use App\Modules\Embassies\Services\EmbassyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmbassyMemberController extends Controller
{
    public function __construct(private readonly EmbassyService $service) {}

    public function index(Request $request): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('manageMembers', $embassy);

        return response()->json(['members' => $this->service->members($embassy)]);
    }

    public function invite(InviteMemberRequest $request): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('invite', $embassy);
        $invitation = $this->service->invite($embassy, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Invitation envoyée.',
            'invitation' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role_in_embassy' => $invitation->role_in_embassy,
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ],
        ], 201);
    }

    public function invitations(Request $request): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('manageMembers', $embassy);

        return response()->json(['invitations' => $this->service->invitations($embassy)]);
    }

    public function revokeInvitation(Request $request, string $invitation): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('manageMembers', $embassy);
        $this->service->revokeInvitation($embassy, $invitation);

        return response()->json(['message' => 'Invitation révoquée.']);
    }

    public function update(UpdateMemberRequest $request, User $member): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('updateMember', $embassy);
        $this->service->updateMember($embassy, $member, $request->validated('role_in_embassy'));

        return response()->json(['message' => 'Rôle du membre mis à jour.']);
    }

    public function destroy(Request $request, User $member): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('removeMember', $embassy);
        $this->service->removeMember($embassy, $member, $request->user());

        return response()->json(['message' => 'Membre retiré de l’ambassade.']);
    }

    private function resolveEmbassy(Request $request): Embassy
    {
        if ($request->filled('embassy_id')) {
            return Embassy::query()->findOrFail($request->string('embassy_id')->toString());
        }

        $embassy = $request->route('embassy');

        return $embassy instanceof Embassy
            ? $embassy
            : $request->user()->embassies()->firstOrFail();
    }
}