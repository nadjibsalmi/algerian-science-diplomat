<?php

namespace App\Modules\Offers\Policies;

use App\Models\User;
use App\Modules\Offers\Models\Offer;

/**
 * AUDIT-CRITICAL: this policy is the actual enforcement mechanism for the
 * SRS's non-negotiable multi-tenant requirement:
 *
 *   "Une ambassade ne peut jamais voir les offres, les candidats, les
 *    documents, les messages d'une autre ambassade."
 *
 * The check is deliberately based on real membership
 * (embassy_user pivot, via $user->embassies()) rather than any
 * client-supplied embassy_id - a user cannot claim to belong to an
 * embassy they aren't actually a member of, regardless of what a request
 * parameter says. Every controller action that touches a specific Offer
 * MUST authorize through this policy (Gate::authorize / $this->authorize
 * in the controller) rather than relying on route-model-binding alone.
 */
class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_offers')
            || $user->hasRole('Super Admin')
            || $user->hasRole('Platform Admin');
    }

    /** Public/candidate visibility of a single published offer - no embassy membership required. */
    public function view(?User $user, Offer $offer): bool
    {
        if ($offer->status === 'published' && $offer->visibility === 'public') {
            return true;
        }

        return $user !== null && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function create(User $user): bool
    {
        return $user->can('create_offer');
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->can('edit_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function submit(User $user, Offer $offer): bool
    {
        return $user->can('edit_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function duplicate(User $user, Offer $offer): bool
    {
        return $user->can('create_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function moderate(User $user, Offer $offer): bool
    {
        return ($user->hasRole('Super Admin') || $user->hasRole('Platform Admin'))
            && $offer->status === 'pending_approval';
    }

    public function moderateAny(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Platform Admin');
    }

    public function publish(User $user, Offer $offer): bool
    {
        return $user->can('publish_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function pause(User $user, Offer $offer): bool
    {
        return $user->can('pause_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function close(User $user, Offer $offer): bool
    {
        return $user->can('close_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $user->can('delete_offer') && $this->belongsToOffersEmbassy($user, $offer);
    }

    /**
     * The actual tenant-isolation check: is this user a real, persisted
     * member of the embassy that owns this specific offer? Super Admins
     * (platform-level, not embassy-level) are the sole, explicit
     * exception - every other role is strictly confined to their own
     * embassy's data, with no bypass.
     */
    private function belongsToOffersEmbassy(User $user, Offer $offer): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->embassies()->where('embassies.id', $offer->embassy_id)->exists();
    }
}
