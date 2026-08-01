<?php

namespace App\Modules\Applications\Notifications;

use App\Modules\Applications\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Application $application) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreferences()
            ->where('notification_type', 'application_status_changed')
            ->first();

        $channels = ['database'];
        if ($prefs?->email ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = __("applications.statuses.{$this->application->status}");
        $offerTitle  = $this->application->offer->title;

        return (new MailMessage)
            ->subject(__('notifications.application_status_changed_subject', ['offer' => $offerTitle]))
            ->greeting(__('mail.greeting', ['name' => $notifiable->firstname]))
            ->line(__('notifications.application_status_body', ['offer' => $offerTitle, 'status' => $statusLabel]))
            ->action(__('notifications.view_application'), route('candidate.applications.show', $this->application))
            ->line(__('mail.footer'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'application_status_changed',
            'application_id' => $this->application->id,
            'offer_id'       => $this->application->offer_id,
            'offer_title'    => $this->application->offer->title,
            'new_status'     => $this->application->status,
        ];
    }
}
