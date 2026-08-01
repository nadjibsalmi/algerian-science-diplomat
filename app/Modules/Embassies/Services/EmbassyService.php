<?php

namespace App\Modules\Embassies\Services;

use App\Models\User;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Embassies\Models\EmbassyInvitation;
use App\Modules\Embassies\Notifications\EmbassyInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmbassyService
{
    public function dashboard(Embassy $embassy): array
    {
        return [
            'embassy' => $this->embassyData($embassy),
            'stats' => [
                'members' => $embassy->users()->count(),
                'offers' => $embassy->offers()->count(),
                'published_offers' => $embassy->offers()->where('status', 'published')->count(),
                'pending_invitations' => $embassy->invitations()
                    ->whereNull('accepted_at')
                    ->where('expires_at', '>', now())
                    ->count(),
            ],
            'recent_offers' => $embassy->offers()
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'slug', 'status', 'deadline', 'created_at']),
        ];
    }

    public function update(Embassy $embassy, array $data): Embassy
    {
        $embassy->update($data);

        return $embassy->refresh();
    }

    public function members(Embassy $embassy)
    {
        return $embassy->users()
            ->select('users.id', 'users.firstname', 'users.lastname', 'users.email', 'users.status')
            ->orderBy('users.lastname')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'status' => $user->status,
                'role_in_embassy' => $user->pivot->role_in_embassy,
            ]);
    }

    public function invite(Embassy $embassy, User $inviter, array $data): EmbassyInvitation
    {
        $email = Str::lower($data['email']);
        $existingMember = $embassy->users()->where('email', $email)->exists();

        if ($existingMember) {
            throw ValidationException::withMessages([
                'email' => 'Cette personne est déjà membre de cette ambassade.',
            ]);
        }

        $plainToken = Str::random(64);
        $invitation = DB::transaction(function () use ($embassy, $inviter, $email, $data, $plainToken): EmbassyInvitation {
            EmbassyInvitation::query()
                ->where('embassy_id', $embassy->id)
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->update(['expires_at' => now()]);

            return EmbassyInvitation::create([
                'embassy_id' => $embassy->id,
                'email' => $email,
                'role_in_embassy' => $data['role_in_embassy'],
                'token' => hash('sha256', $plainToken),
                'invited_by' => $inviter->id,
                'expires_at' => now()->addHours(config('asd.invitations.expiry_hours', 48)),
            ]);
        });

        Notification::route('mail', $invitation->email)
            ->notify(new EmbassyInvitationNotification($invitation, $plainToken));

        return $invitation;
    }

    public function invitations(Embassy $embassy)
    {
        return $embassy->invitations()
            ->with('inviter:id,firstname,lastname')
            ->latest()
            ->get()
            ->map(fn (EmbassyInvitation $invitation): array => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role_in_embassy' => $invitation->role_in_embassy,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'accepted_at' => $invitation->accepted_at?->toIso8601String(),
                'invited_by' => $invitation->inviter?->full_name,
                'usable' => $invitation->isUsable(),
            ]);
    }

    public function revokeInvitation(Embassy $embassy, string $invitationId): void
    {
        $invitation = $embassy->invitations()->findOrFail($invitationId);

        if ($invitation->accepted_at === null) {
            $invitation->forceFill(['expires_at' => now()])->save();
        }
    }

    public function acceptInvitation(EmbassyInvitation $invitation, User $user): Embassy
    {
        if (! $invitation->isUsable()) {
            throw ValidationException::withMessages([
                'invitation' => 'Cette invitation est expirée ou a déjà été utilisée.',
            ]);
        }

        if (Str::lower($user->email) !== Str::lower($invitation->email)) {
            throw ValidationException::withMessages([
                'invitation' => 'Cette invitation est liée à une autre adresse email.',
            ]);
        }

        DB::transaction(function () use ($invitation, $user): void {
            $invitation->embassy->users()->syncWithoutDetaching([
                $user->id => ['role_in_embassy' => $invitation->role_in_embassy],
            ]);
            $user->assignRole($this->spatieRoleFor($invitation->role_in_embassy));
            $invitation->forceFill(['accepted_at' => now()])->save();
        });

        return $invitation->embassy->refresh();
    }

    public function updateMember(Embassy $embassy, User $member, string $role): void
    {
        $this->ensureMember($embassy, $member);
        $embassy->users()->updateExistingPivot($member->id, ['role_in_embassy' => $role]);
    }

    public function removeMember(Embassy $embassy, User $member, User $actor): void
    {
        $this->ensureMember($embassy, $member);

        if ($member->is($actor)) {
            throw ValidationException::withMessages([
                'member' => 'Vous ne pouvez pas retirer votre propre accès.',
            ]);
        }

        $embassy->users()->detach($member->id);
    }

    private function ensureMember(Embassy $embassy, User $member): void
    {
        if (! $embassy->users()->whereKey($member->id)->exists()) {
            throw ValidationException::withMessages([
                'member' => 'Cet utilisateur n’est pas membre de cette ambassade.',
            ]);
        }
    }

    private function spatieRoleFor(string $role): string
    {
        return match ($role) {
            'director' => 'Embassy Director',
            'recruiter' => 'Embassy Recruiter',
            'hr' => 'Embassy HR',
        };
    }

    private function embassyData(Embassy $embassy): array
    {
        return [
            'id' => $embassy->id,
            'country' => $embassy->country,
            'official_name' => $embassy->official_name,
            'logo' => $embassy->logo,
            'email' => $embassy->email,
            'phone' => $embassy->phone,
            'website' => $embassy->website,
            'address' => $embassy->address,
            'verified' => $embassy->verified,
            'status' => $embassy->status,
        ];
    }
}