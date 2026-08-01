<?php

namespace App\Modules\Messaging\Policies;

use App\Models\User;
use App\Modules\Messaging\Models\Message;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $user->can('view', $message->conversation);
    }

    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id
            && $message->created_at?->gt(now()->subMinutes(15));
    }
}