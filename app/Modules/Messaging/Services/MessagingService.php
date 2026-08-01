<?php

namespace App\Modules\Messaging\Services;

use App\Models\User;
use App\Modules\Documents\Models\Document;
use App\Modules\Messaging\Jobs\SendMessageNotificationJob;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessagingService
{
    public function conversations(User $user)
    {
        $query = Conversation::query()
            ->with(['application.offer:id,title', 'application.candidate:id,firstname,lastname']);

        if ($user->hasRole('Super Admin')) {
            // Platform administrators can audit every conversation.
        } elseif ($user->can('review_application')) {
            $query->whereHas('application.offer.embassy.users', fn ($q) => $q->whereKey($user->id));
        } else {
            $query->whereHas('application', fn ($application) => $application->where('user_id', $user->id));
        }

        return $query
            ->withCount(['messages as unread_count' => function ($query) use ($user): void {
                $query->where('sender_id', '!=', $user->id)
                    ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id));
            }])
            ->latest('last_message_at')
            ->paginate(20);
    }

    public function conversation(User $user, Conversation $conversation): Conversation
    {
        $this->authorizeConversation($user, $conversation);

        $conversation->load('application.offer', 'application.candidate');
        $conversation->setRelation('messages', $conversation->messages()->with('sender', 'attachments')->get());

        return $conversation;
    }

    public function send(User $sender, Conversation $conversation, string $body, array $attachmentIds = []): Message
    {
        $this->authorizeConversation($sender, $conversation);
        if ($conversation->status !== 'open') {
            throw ValidationException::withMessages(['conversation' => 'Cette conversation est fermée.']);
        }

        $this->validateAttachments($sender, $conversation, $attachmentIds);

        return DB::transaction(function () use ($sender, $conversation, $body, $attachmentIds): Message {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => $body,
                'type' => 'text',
            ]);

            if (! empty($attachmentIds)) {
                $message->attachments()->sync($attachmentIds);
            }

            $conversation->update(['last_message_at' => now()]);
            SendMessageNotificationJob::dispatch($message)->afterCommit();

            return $message->load('sender', 'attachments');
        });
    }

    public function markRead(User $user, Conversation $conversation): int
    {
        $this->authorizeConversation($user, $conversation);
        $count = 0;

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->chunkById(100, function ($messages) use ($user, &$count): void {
                foreach ($messages as $message) {
                    $message->markReadBy($user->id);
                    $count++;
                }
            });

        return $count;
    }

    public function archive(User $user, Conversation $conversation): Conversation
    {
        $this->authorizeConversation($user, $conversation);
        $conversation->update(['status' => 'archived']);

        return $conversation->refresh();
    }

    private function authorizeConversation(User $user, Conversation $conversation): void
    {
        $application = $conversation->loadMissing('application.offer.embassy.users')->application;
        $isCandidate = $application->user_id === $user->id;
        $isEmbassyMember = $application->offer->embassy->users->contains('id', $user->id)
            && $user->can('review_application');

        abort_unless($isCandidate || $isEmbassyMember || $user->hasRole('Super Admin'), 403);
    }

    private function validateAttachments(User $sender, Conversation $conversation, array $attachmentIds): void
    {
        if ($attachmentIds === []) {
            return;
        }

        $application = $conversation->loadMissing('application')->application;
        $count = Document::query()
            ->whereIn('id', $attachmentIds)
            ->where('status', Document::STATUS_CLEAN)
            ->where(function ($query) use ($sender, $application): void {
                $query->where('user_id', $sender->id)
                    ->orWhereHas('applications', fn ($q) => $q->whereKey($application->id));
            })
            ->count();

        if ($count !== count(array_unique($attachmentIds))) {
            throw ValidationException::withMessages([
                'attachment_ids' => 'Les pièces jointes ne sont pas accessibles dans cette conversation.',
            ]);
        }
    }
}