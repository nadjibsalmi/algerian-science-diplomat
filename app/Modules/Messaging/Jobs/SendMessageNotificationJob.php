<?php

namespace App\Modules\Messaging\Jobs;

use App\Modules\Messaging\Models\Message;
use App\Modules\Notifications\Notifications\NewMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly Message $message) {}

    public function handle(): void
    {
        $message = $this->message->load('conversation.application.candidate', 'conversation.application.offer.embassy.users');
        $application = $message->conversation->application;
        $recipients = collect([$application->candidate])
            ->merge($application->offer->embassy->users)
            ->unique('id')
            ->reject(fn ($user) => $user->id === $message->sender_id);

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewMessageNotification($message));
        }
    }
}