<?php

namespace App\Modules\Administration\Policies;

use App\Models\User;

class AdministrationPolicy
{
    public function access(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Platform Admin') || $user->can('manage_administration');
    }
}