<?php

namespace App\Modules\Offers\Services;

use App\Models\User;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Offers\Models\Offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function create(User $actor, array $data): Offer
    {
        $embassy = $this->embassyForActor($actor, $data['embassy_id']);

        return Offer::create([
            ...$data,
            'embassy_id' => $embassy->id,
            'status' => 'draft',
            'moderation_status' => 'not_reviewed',
        ]);
    }

    public function update(Offer $offer, array $data): Offer
    {
        $this->ensureEditable($offer);
        $offer->update($data);

        return $offer->refresh();
    }

    public function submit(Offer $offer, User $actor): Offer
    {
        $this->ensureTransition($offer, ['draft', 'rejected']);
        $offer->forceFill([
            'status' => 'pending_approval',
            'moderation_status' => 'pending',
            'moderation_notes' => null,
            'submitted_by' => $actor->id,
            'submitted_at' => now(),
        ])->save();

        return $offer->refresh();
    }

    public function moderate(Offer $offer, User $moderator, string $decision, ?string $notes): Offer
    {
        $this->ensureTransition($offer, ['pending_approval']);

        $offer->forceFill([
            'status' => $decision === 'approve' ? 'approved' : 'draft',
            'moderation_status' => $decision === 'approve' ? 'approved' : 'rejected',
            'moderation_notes' => $notes,
            'moderated_by' => $moderator->id,
            'published_at' => null,
        ])->save();

        return $offer->refresh();
    }

    public function publish(Offer $offer): Offer
    {
        $this->ensureTransition($offer, ['approved']);
        $offer->forceFill([
            'status' => 'published',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ])->save();

        return $offer->refresh();
    }

    public function pause(Offer $offer): Offer
    {
        $this->ensureTransition($offer, ['published']);
        $offer->update(['status' => 'paused']);

        return $offer->refresh();
    }

    public function close(Offer $offer): Offer
    {
        $this->ensureTransition($offer, ['published', 'paused']);
        $offer->update(['status' => 'closed', 'closed_at' => now()]);

        return $offer->refresh();
    }

    public function duplicate(Offer $source, User $actor): Offer
    {
        $this->embassyForActor($actor, $source->embassy_id);
        $copy = $source->replicate([
            'slug',
            'status',
            'moderation_status',
            'moderation_notes',
            'submitted_by',
            'moderated_by',
            'submitted_at',
            'published_at',
            'closed_at',
        ]);
        $copy->forceFill([
            'slug' => Str::slug($source->title).'-'.Str::lower(Str::random(8)),
            'status' => 'draft',
            'moderation_status' => 'not_reviewed',
            'duplicated_from_id' => $source->id,
            'deadline' => $source->deadline?->copy()->isFuture()
                ? $source->deadline->copy()
                : null,
        ])->save();

        return $copy;
    }

    public function expire(): int
    {
        return DB::transaction(function (): int {
            $count = 0;
            Offer::query()->expired()->chunkById(100, function ($offers) use (&$count): void {
                foreach ($offers as $offer) {
                    $offer->forceFill([
                        'status' => 'closed',
                        'closed_at' => now(),
                    ])->save();
                    $count++;
                }
            });

            return $count;
        });
    }

    private function ensureEditable(Offer $offer): void
    {
        if (! in_array($offer->status, ['draft', 'rejected', 'paused'], true)) {
            throw ValidationException::withMessages([
                'offer' => 'Cette offre ne peut plus être modifiée dans son état actuel.',
            ]);
        }
    }

    private function ensureTransition(Offer $offer, array $allowed): void
    {
        if (! in_array($offer->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Transition impossible depuis l’état '.$offer->status.'.',
            ]);
        }
    }

    private function embassyForActor(User $actor, string $embassyId): Embassy
    {
        if ($actor->hasRole('Super Admin') || $actor->hasRole('Platform Admin')) {
            return Embassy::query()->findOrFail($embassyId);
        }

        return $actor->embassies()->whereKey($embassyId)->firstOrFail();
    }
}