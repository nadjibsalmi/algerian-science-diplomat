<?php

namespace App\Modules\CMS\Policies;

use App\Models\User;

class CmsPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Platform Admin') || $user->can('manage_cms');
    }
}