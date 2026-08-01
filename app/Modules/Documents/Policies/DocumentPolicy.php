<?php

namespace App\Modules\Documents\Policies;

use App\Models\User;
use App\Modules\Documents\Models\Document;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    /** Candidate can view their own documents; embassy admin can view documents attached to their applications */
    public function view(User $user, Document $document): bool
    {
        if ($user->id === $document->user_id) {
            return true;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Embassy admin can only see documents explicitly attached to an application for their embassy
        if ($user->can('download_documents')) {
            return $document->applications()
                ->whereHas('offer.embassy', fn ($q) => $q->whereHas('users', fn ($q2) => $q2->where('users.id', $user->id)))
                ->exists();
        }

        return false;
    }

    public function upload(User $user): bool
    {
        return $user->id !== null; // Any authenticated user
    }

    public function share(User $user, Document $document): bool
    {
        return $user->id === $document->user_id || $user->hasRole('Super Admin');
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->id === $document->user_id) {
            return true;
        }

        return $user->hasRole('Super Admin');
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
