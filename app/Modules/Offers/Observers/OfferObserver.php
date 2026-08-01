<?php

namespace App\Modules\Offers\Observers;

use App\Modules\Offers\Models\Offer;
use Illuminate\Support\Str;

class OfferObserver
{
    public function creating(Offer $offer): void
    {
        if ($offer->slug === null || $offer->slug === '') {
            $offer->slug = Str::slug($offer->title).'-'.Str::lower(Str::random(8));
        }
    }

    public function updating(Offer $offer): void
    {
        if ($offer->isDirty('status')) {
            if ($offer->status === 'published' && $offer->published_at === null) {
                $offer->published_at = now();
            }

            if ($offer->status === 'closed' && $offer->closed_at === null) {
                $offer->closed_at = now();
            }

            if ($offer->status !== 'closed' && $offer->closed_at !== null) {
                $offer->closed_at = null;
            }
        }
    }
}