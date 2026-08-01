<?php

namespace App\Modules\Notifications\Notifications;

use App\Modules\Offers\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SearchAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Offer $offer, public readonly string $alertName) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle offre correspondant à « {$this->alertName} »")
            ->line($this->offer->title)
            ->action('Voir l’offre', route('offers.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'search_alert',
            'alert_name' => $this->alertName,
            'offer_id' => $this->offer->id,
            'offer_title' => $this->offer->title,
        ];
    }
}