<?php

namespace App\Modules\Analytics\Services;

use App\Models\User;
use App\Modules\Analytics\Models\AnalyticsEvent;
use App\Modules\Offers\Models\Offer;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function record(?User $user, string $event, array $properties = [], ?string $subjectType = null, ?string $subjectId = null, ?string $ip = null): AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'user_id' => $user?->id,
            'event' => $event,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'properties' => $properties,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
        ]);
    }

    public function dashboard(?string $from = null, ?string $to = null): array
    {
        $events = AnalyticsEvent::query()->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to));
        return [
            'events_total' => (clone $events)->count(),
            'events_by_type' => (clone $events)->select('event', DB::raw('count(*) as total'))->groupBy('event')->orderByDesc('total')->get(),
            'top_offers' => DB::table('offer_views')->join('offers', 'offers.id', '=', 'offer_views.offer_id')
                ->select('offers.id', 'offers.title', DB::raw('count(offer_views.id) as views_count'))
                ->when($from, fn ($q) => $q->where('offer_views.created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('offer_views.created_at', '<=', $to))
                ->groupBy('offers.id', 'offers.title')->orderByDesc('views_count')->limit(10)->get(),
            'offer_views' => DB::table('offer_views')->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))->count(),
        ];
    }
}