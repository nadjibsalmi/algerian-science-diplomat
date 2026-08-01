<?php

namespace App\Modules\Applications\Policies;

use App\Models\User;
use App\Modules\Applications\Models\Application;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    /** Candidate can view their own applications; Embassy admin can view applications to their offers */
    public function view(User $user, Application $application): bool
    {
        if ($user->id === $application->user_id) {
            return true;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Embassy admin: can only see applications for their own embassy's offers
        return $user->can('review_application')
            && $user->embassies()->where('embassies.id', $application->offer->embassy_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Candidate') || $user->hasRole('Super Admin');
    }

    public function attachDocuments(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            && ! in_array($application->status, ['accepted', 'rejected', 'withdrawn'], true);
    }

    public function updateStatus(User $user, Application $application): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->can('review_application')
            && $user->embassies()->where('embassies.id', $application->offer->embassy_id)->exists();
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }

    public function evaluate(User $user, Application $application): bool
    {
        return $this->updateStatus($user, $application);
    }
}
