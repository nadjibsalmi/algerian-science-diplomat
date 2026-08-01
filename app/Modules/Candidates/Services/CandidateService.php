<?php

namespace App\Modules\Candidates\Services;

use App\Models\User;
use App\Modules\Candidates\Models\CandidateAward;
use App\Modules\Candidates\Models\CandidateEducation;
use App\Modules\Candidates\Models\CandidateExperience;
use App\Modules\Candidates\Models\CandidateLanguage;
use App\Modules\Candidates\Models\CandidateProfile;
use App\Modules\Candidates\Models\CandidatePublication;
use App\Modules\Candidates\Models\CandidateSkill;
use Illuminate\Support\Facades\DB;

class CandidateService
{
    private const SECTIONS = [
        'education' => [CandidateEducation::class, 'educations'],
        'educations' => [CandidateEducation::class, 'educations'],
        'experience' => [CandidateExperience::class, 'experiences'],
        'experiences' => [CandidateExperience::class, 'experiences'],
        'language' => [CandidateLanguage::class, 'languages'],
        'languages' => [CandidateLanguage::class, 'languages'],
        'skill' => [CandidateSkill::class, 'skills'],
        'skills' => [CandidateSkill::class, 'skills'],
        'award' => [CandidateAward::class, 'awards'],
        'awards' => [CandidateAward::class, 'awards'],
        'publication' => [CandidatePublication::class, 'publications'],
        'publications' => [CandidatePublication::class, 'publications'],
    ];

    public function profile(User $user): CandidateProfile
    {
        return CandidateProfile::firstOrCreate(['user_id' => $user->id]);
    }

    public function dashboard(User $user): array
    {
        $profile = $this->profile($user)->load([
            'educations', 'experiences', 'languages', 'skills', 'awards', 'publications',
        ]);

        return [
            'profile' => $profile,
            'favorites_count' => DB::table('offer_favorites')->where('user_id', $user->id)->count(),
            'sections' => [
                'education' => $profile->educations->count(),
                'experience' => $profile->experiences->count(),
                'language' => $profile->languages->count(),
                'skill' => $profile->skills->count(),
                'award' => $profile->awards->count(),
                'publication' => $profile->publications->count(),
            ],
        ];
    }

    public function updateProfile(User $user, array $data): CandidateProfile
    {
        $profile = $this->profile($user);
        $profile->update($data);
        $profile->recalculateCompleteness();

        return $profile->refresh();
    }

    public function entries(User $user, string $section)
    {
        [, $relation] = $this->section($section);

        return $this->profile($user)->{$relation}()->get();
    }

    public function createEntry(User $user, string $section, array $data)
    {
        [, $relation] = $this->section($section);
        $entry = $this->profile($user)->{$relation}()->create($data);
        $this->profile($user)->recalculateCompleteness();

        return $entry;
    }

    public function updateEntry(User $user, string $section, string $id, array $data)
    {
        $entry = $this->entry($user, $section, $id);
        $entry->update($data);
        $this->profile($user)->recalculateCompleteness();

        return $entry->refresh();
    }

    public function deleteEntry(User $user, string $section, string $id): void
    {
        $this->entry($user, $section, $id)->delete();
        $this->profile($user)->recalculateCompleteness();
    }

    public function toggleFavorite(User $user, string $offerId): bool
    {
        $favorite = DB::table('offer_favorites')
            ->where('offer_favorites.user_id', $user->id)
            ->where('offer_id', $offerId)
            ->first();

        if ($favorite !== null) {
            DB::table('offer_favorites')->where('id', $favorite->id)->delete();

            return false;
        }

        DB::table('offer_favorites')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'offer_id' => $offerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    public function favorites(User $user)
    {
        return DB::table('offer_favorites')
            ->join('offers', 'offers.id', '=', 'offer_favorites.offer_id')
            ->where('offer_favorites.user_id', $user->id)
            ->select([
                'offer_favorites.id',
                'offer_favorites.offer_id',
                'offer_favorites.created_at',
                'offers.title',
                'offers.slug',
                'offers.country',
                'offers.city',
                'offers.deadline',
            ])
            ->orderByDesc('offer_favorites.created_at')
            ->paginate(20);
    }

    private function entry(User $user, string $section, string $id)
    {
        [, $relation] = $this->section($section);

        return $this->profile($user)->{$relation}()->whereKey($id)->firstOrFail();
    }

    private function section(string $section): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        return self::SECTIONS[$section];
    }
}