<?php

namespace App\Modules\Embassies\Notifications;

use App\Modules\Embassies\Models\EmbassyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmbassyInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly EmbassyInvitation $invitation,
        public readonly string $plainToken,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation à rejoindre '.$this->invitation->embassy->official_name)
            ->greeting('Vous êtes invité à rejoindre une ambassade')
            ->line('L’ambassade '.$this->invitation->embassy->official_name.' vous invite comme '.$this->invitation->role_in_embassy.'.')
            ->action('Accepter l’invitation', route('embassy.invitations.show', $this->plainToken))
            ->line('Cette invitation expire le '.$this->invitation->expires_at->translatedFormat('d/m/Y à H:i').'.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'embassy_id' => $this->invitation->embassy_id,
            'role_in_embassy' => $this->invitation->role_in_embassy,
            'expires_at' => $this->invitation->expires_at->toIso8601String(),
        ];
    }
}