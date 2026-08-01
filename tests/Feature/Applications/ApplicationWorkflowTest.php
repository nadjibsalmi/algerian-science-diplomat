<?php

namespace Tests\Feature\Applications;

use App\Models\User;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Services\ApplicationService;
use App\Modules\Offers\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_application_status_transition_is_rejected(): void
    {
        $candidate = User::factory()->create();
        $offer = Offer::factory()->published()->create();
        $application = Application::create([
            'offer_id' => $offer->id,
            'user_id' => $candidate->id,
            'status' => 'submitted',
            'submitted_at' => now(),
            'eligibility_passed' => true,
        ]);

        $this->expectException(ValidationException::class);
        app(ApplicationService::class)->changeStatus($application, 'accepted', $candidate);
    }

    public function test_candidate_can_only_attach_documents_through_their_application_policy(): void
    {
        $candidate = User::factory()->create();
        $otherCandidate = User::factory()->create();
        $offer = Offer::factory()->published()->create();
        $application = Application::create([
            'offer_id' => $offer->id,
            'user_id' => $candidate->id,
            'status' => 'submitted',
        ]);

        $this->assertTrue($candidate->can('attachDocuments', $application));
        $this->assertFalse($otherCandidate->can('attachDocuments', $application));
    }
}