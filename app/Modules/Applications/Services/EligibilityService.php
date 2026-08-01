<?php

namespace App\Modules\Applications\Services;

use App\Models\User;
use App\Modules\Offers\Models\Offer;

class EligibilityService
{
    /**
     * Check whether a candidate meets all eligibility requirements for an offer.
     * Returns ['passed' => bool, 'details' => [['rule', 'passed', 'reason']]]
     */
    public function check(User $candidate, Offer $offer): array
    {
        $profile = $candidate->candidateProfile;
        $checks  = [];

        // 1. Offer must be published and not expired
        if ($offer->status !== 'published') {
            return ['passed' => false, 'details' => [['rule' => 'offer_status', 'passed' => false, 'reason' => __('eligibility.offer_not_published')]]];
        }

        if ($offer->deadline !== null && $offer->deadline->isPast()) {
            return ['passed' => false, 'details' => [['rule' => 'deadline', 'passed' => false, 'reason' => __('eligibility.deadline_passed')]]];
        }

        // 2. Level requirement
        if ($offer->level !== null && $profile !== null) {
            $levels = ['licence' => 1, 'master' => 2, 'doctorat' => 3, 'postdoc' => 4, 'professional' => 0];
            $required = $levels[$offer->level] ?? 0;
            $candidate_level = $levels[$profile->current_level ?? ''] ?? 0;
            $passed = $candidate_level >= $required;
            $checks[] = ['rule' => 'level', 'passed' => $passed, 'reason' => $passed ? null : __('eligibility.level_insufficient', ['required' => $offer->level])];
        }

        // 3. Age requirement (if set via offer metadata)
        if ($profile?->birth_date !== null && $offer->min_age !== null) {
            $age    = $profile->birth_date->age;
            $passed = $age >= $offer->min_age && ($offer->max_age === null || $age <= $offer->max_age);
            $checks[] = ['rule' => 'age', 'passed' => $passed, 'reason' => $passed ? null : __('eligibility.age_not_met')];
        }

        $allPassed = empty(array_filter($checks, fn ($c) => ! $c['passed']));

        return ['passed' => $allPassed, 'details' => $checks];
    }
}
