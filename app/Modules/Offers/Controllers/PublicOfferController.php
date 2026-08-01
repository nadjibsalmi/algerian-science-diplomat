<?php

namespace App\Modules\Offers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offers\Models\Offer;
use Inertia\Inertia;
use Inertia\Response;

/**
 * First real vertical slice of the frontend: public, unauthenticated
 * offers listing. Deliberately the smallest possible page that still
 * exercises the full stack end-to-end (PostgreSQL -> Eloquent -> Inertia
 * -> Vue -> Tailwind), per ARCHITECTURE.md's recommended next steps.
 */
class PublicOfferController extends Controller
{
    public function index(): Response
    {
        $offers = Offer::query()
            ->published()
            ->with('embassy:id,official_name')
            ->latest('published_at')
            ->paginate(12)
            ->through(function (Offer $offer): array {
                $embassyName = '';

                if ($offer->embassy !== null) {
                    $embassyName = $offer->embassy->official_name;
                }

                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'slug' => $offer->slug,
                    'country' => $offer->country,
                    'city' => $offer->city,
                    'offer_type' => $offer->offer_type,
                    'level' => $offer->level,
                    'deadline' => $offer->deadline?->toIso8601String(),
                    'embassy_name' => $embassyName,
                ];
            });

        return Inertia::render('Offers/Index', [
            'offers' => $offers,
        ]);
    }
}
