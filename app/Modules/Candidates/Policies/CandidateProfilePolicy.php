<?php

namespace App\Modules\Candidates\Policies;

use App\Models\User;
use App\Modules\Candidates\Models\CandidateProfile;

class CandidateProfilePolicy
{
    public function view(User $user, CandidateProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function update(User $user, CandidateProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}