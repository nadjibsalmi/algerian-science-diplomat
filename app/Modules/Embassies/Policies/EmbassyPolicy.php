<?php

namespace App\Modules\Embassies\Policies;

use App\Models\User;
use App\Modules\Embassies\Models\Embassy;

class EmbassyPolicy
{
    public function viewDashboard(User $user, Embassy $embassy): bool
    {
        return $this->isMemberOrPlatformAdmin($user, $embassy);
    }

    public function update(User $user, Embassy $embassy): bool
    {
        return $this->isPrivilegedPlatformUser($user)
            || $this->isDirector($user, $embassy)
            || ($user->can('manage_settings') && $this->isMember($user, $embassy));
    }

    public function manageMembers(User $user, Embassy $embassy): bool
    {
        return $this->isPrivilegedPlatformUser($user)
            || $this->isDirector($user, $embassy)
            || ($user->can('manage_users') && $this->isMember($user, $embassy));
    }

    public function invite(User $user, Embassy $embassy): bool
    {
        return $this->manageMembers($user, $embassy);
    }

    public function updateMember(User $user, Embassy $embassy): bool
    {
        return $this->manageMembers($user, $embassy);
    }

    public function removeMember(User $user, Embassy $embassy): bool
    {
        return $this->manageMembers($user, $embassy);
    }

    private function isMemberOrPlatformAdmin(User $user, Embassy $embassy): bool
    {
        return $this->isPrivilegedPlatformUser($user)
            || $this->isMember($user, $embassy);
    }

    private function isMember(User $user, Embassy $embassy): bool
    {
        return $user->embassies()->whereKey($embassy->getKey())->exists();
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    private function isPrivilegedPlatformUser(User $user): bool
    {
        return $this->isSuperAdmin($user) || $user->hasRole('Platform Admin');
    }

    private function isDirector(User $user, Embassy $embassy): bool
    {
        return $embassy->users()
            ->whereKey($user->getKey())
            ->wherePivot('role_in_embassy', 'director')
            ->exists();
    }
}