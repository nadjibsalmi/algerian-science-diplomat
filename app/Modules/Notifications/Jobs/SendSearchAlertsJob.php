<?php

namespace App\Modules\Notifications\Jobs;

use App\Modules\Notifications\Notifications\SearchAlertNotification;
use App\Modules\Notifications\Models\SearchAlert;
use App\Modules\Offers\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSearchAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        SearchAlert::query()->where('active', true)->with('user')->chunkById(100, function ($alerts): void {
            foreach ($alerts as $alert) {
                $query = Offer::query()->published();
                foreach ($alert->filters ?? [] as $field => $value) {
                    if (is_scalar($value) && in_array($field, ['country', 'city', 'offer_type', 'category', 'research_field', 'level'], true)) {
                        $query->where($field, $value);
                    }
                }

                $offer = $query->latest('published_at')->first();
                if ($offer !== null) {
                    $alert->user->notify(new SearchAlertNotification($offer, $alert->name));
                    $alert->update(['last_sent_at' => now()]);
                }
            }
        });
    }
}