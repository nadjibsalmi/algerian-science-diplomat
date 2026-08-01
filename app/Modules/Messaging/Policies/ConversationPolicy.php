<?php

namespace App\Modules\Messaging\Policies;

use App\Models\User;
use App\Modules\Messaging\Models\Conversation;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $application = $conversation->application;

        // Candidate: can view their own application conversation
        if ($user->id === $application->user_id) {
            return true;
        }

        // Embassy admin: can view conversations for their embassy's applications
        return $user->can('review_application')
            && $user->embassies()->where('embassies.id', $application->offer->embassy_id)->exists();
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation)
            && $conversation->status === 'open';
    }
}
