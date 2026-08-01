<?php

namespace App\Modules\Notifications\Jobs;

use App\Modules\Notifications\Models\NotificationDigest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        NotificationDigest::query()
            ->with('user')
            ->whereIn('frequency', ['daily', 'weekly'])
            ->chunkById(100, function ($digests): void {
                foreach ($digests as $digest) {
                    $user = $digest->user;
                    $notifications = $user->unreadNotifications()->latest()->limit(20)->get();

                    if ($notifications->isEmpty()) {
                        continue;
                    }

                    $user->notify(new class($notifications, $digest->frequency) extends \Illuminate\Notifications\Notification implements ShouldQueue {
                        use Queueable;

                        public function __construct(
                            private readonly mixed $notifications,
                            private readonly string $frequency,
                        ) {}

                        public function via(object $notifiable): array
                        {
                            return ['mail'];
                        }

                        public function toMail(object $notifiable): MailMessage
                        {
                            return (new MailMessage)
                                ->subject('Votre digest de notifications ASD')
                                ->line("Voici vos notifications {$this->frequency}.")
                                ->line("Nombre de notifications non lues : {$this->notifications->count()}.");
                        }

                        public function toArray(object $notifiable): array
                        {
                            return [
                                'type' => 'notification_digest',
                                'frequency' => $this->frequency,
                                'count' => $this->notifications->count(),
                            ];
                        }
                    });
                }
            });
    }
}