<?php

namespace Tests\Feature\Offers;

use App\Models\User;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Offers\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AUDIT-CRITICAL test suite: proves the SRS's non-negotiable multi-tenant
 * requirement actually holds in code, not just in a policy comment.
 *
 *   "Une ambassade ne peut jamais voir les offres, les candidats, les
 *    documents, les messages d'une autre ambassade."
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_cannot_update_another_embassys_offer(): void
    {
        $embassyA = Embassy::factory()->create();
        $embassyB = Embassy::factory()->create();

        $recruiterA = User::factory()->create();
        $recruiterA->embassies()->attach($embassyA->id, ['role_in_embassy' => 'recruiter']);
        $recruiterA->givePermissionTo('edit_offer');

        $offerBelongingToEmbassyB = Offer::factory()->create(['embassy_id' => $embassyB->id]);

        $this->assertFalse(
            $recruiterA->can('update', $offerBelongingToEmbassyB),
            'A recruiter from Embassy A must never be authorized to update an offer belonging to Embassy B.'
        );
    }

    public function test_recruiter_can_update_their_own_embassys_offer(): void
    {
        $embassy = Embassy::factory()->create();

        $recruiter = User::factory()->create();
        $recruiter->embassies()->attach($embassy->id, ['role_in_embassy' => 'recruiter']);
        $recruiter->givePermissionTo('edit_offer');

        $ownOffer = Offer::factory()->create(['embassy_id' => $embassy->id]);

        $this->assertTrue(
            $recruiter->can('update', $ownOffer),
            'A recruiter must be authorized to update an offer belonging to their own embassy.'
        );
    }

    public function test_super_admin_can_access_any_embassys_offer(): void
    {
        $embassy = Embassy::factory()->create();
        $offer = Offer::factory()->create(['embassy_id' => $embassy->id]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->assertTrue(
            $superAdmin->can('update', $offer),
            'Super Admin is the sole, explicit exception to embassy-level isolation.'
        );
    }

    public function test_scope_for_embassy_never_returns_another_embassys_offers(): void
    {
        $embassyA = Embassy::factory()->create();
        $embassyB = Embassy::factory()->create();

        Offer::factory()->count(3)->create(['embassy_id' => $embassyA->id]);
        Offer::factory()->count(2)->create(['embassy_id' => $embassyB->id]);

        $results = Offer::forEmbassy($embassyA->id)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->every(fn (Offer $o) => $o->embassy_id === $embassyA->id));
    }
}
