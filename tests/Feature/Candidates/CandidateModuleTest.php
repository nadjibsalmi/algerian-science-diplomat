<?php

namespace Tests\Feature\Candidates;

use App\Models\User;
use App\Modules\Candidates\Models\CandidateProfile;
use App\Modules\Candidates\Services\CandidateService;
use App\Modules\Offers\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CandidateModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_profile_is_isolated_from_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $profile = CandidateProfile::create(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $profile));
        $this->assertFalse($other->can('view', $profile));
        $this->assertFalse($other->can('update', $profile));
    }

    public function test_profile_completeness_recalculates_after_adding_education(): void
    {
        $user = User::factory()->create();
        $profile = CandidateProfile::create([
            'user_id' => $user->id,
            'wilaya' => 'Alger',
            'birth_date' => '1995-01-01',
            'bio' => 'Chercheur',
        ]);

        app(CandidateService::class)->createEntry($user, 'education', [
            'institution' => 'USTHB',
            'degree' => 'master',
            'field' => 'Informatique',
            'start_year' => 2015,
            'end_year' => 2017,
            'current' => false,
        ]);

        $this->assertSame(30, $profile->fresh()->completeness_pct);
    }

    public function test_favorites_are_scoped_to_the_authenticated_candidate(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $offer = Offer::factory()->published()->create();

        $this->assertTrue(app(CandidateService::class)->toggleFavorite($first, $offer->id));
        $this->assertFalse(app(CandidateService::class)->toggleFavorite($first, $offer->id));
        $this->assertTrue(app(CandidateService::class)->toggleFavorite($second, $offer->id));

        $this->assertSame(0, DB::table('offer_favorites')->where('user_id', $first->id)->count());
        $this->assertSame(1, DB::table('offer_favorites')->where('user_id', $second->id)->count());
    }
}