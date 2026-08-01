<?php

namespace App\Modules\Notifications\Notifications;

use App\Modules\Messaging\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Message $message) {}

    public function via(object $notifiable): array
    {
        $preference = DB::table('notification_preferences')
            ->where('user_id', $notifiable->getKey())
            ->where('notification_type', 'new_message')
            ->first();
        $channels = ($preference?->in_app ?? true) ? ['database'] : [];

        if ($preference?->email ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau message concernant votre candidature')
            ->line('Vous avez reçu un nouveau message.')
            ->action('Ouvrir la conversation', route('messaging.conversations.show', $this->message->conversation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'preview' => mb_substr($this->message->body, 0, 160),
        ];
    }
}