<?php

namespace Tests\Feature\Messaging;

use App\Models\User;
use App\Modules\Applications\Models\Application;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Services\MessagingService;
use App\Modules\Offers\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MessagingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_send_in_their_application_conversation(): void
    {
        $candidate = User::factory()->create();
        $offer = Offer::factory()->published()->create();
        $application = Application::create([
            'offer_id' => $offer->id,
            'user_id' => $candidate->id,
            'status' => 'submitted',
        ]);
        $conversation = Conversation::create(['application_id' => $application->id]);

        $message = app(MessagingService::class)->send($candidate, $conversation, 'Bonjour');

        $this->assertSame('Bonjour', $message->body);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $candidate->id,
        ]);
    }

    public function test_unrelated_user_cannot_send_to_a_conversation(): void
    {
        $candidate = User::factory()->create();
        $outsider = User::factory()->create();
        $offer = Offer::factory()->published()->create();
        $application = Application::create([
            'offer_id' => $offer->id,
            'user_id' => $candidate->id,
            'status' => 'submitted',
        ]);
        $conversation = Conversation::create(['application_id' => $application->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(MessagingService::class)->send($outsider, $conversation, 'Intrusion');
    }

    public function test_only_clean_accessible_documents_can_be_attached(): void
    {
        $candidate = User::factory()->create();
        $other = User::factory()->create();
        $offer = Offer::factory()->published()->create();
        $application = Application::create([
            'offer_id' => $offer->id,
            'user_id' => $candidate->id,
            'status' => 'submitted',
        ]);
        $conversation = Conversation::create(['application_id' => $application->id]);
        $document = \App\Modules\Documents\Models\Document::create([
            'user_id' => $other->id,
            'type' => 'cv',
            'name' => 'CV',
            'original_filename' => 'cv.pdf',
            'path' => 'cv.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10,
            'status' => \App\Modules\Documents\Models\Document::STATUS_CLEAN,
        ]);

        $this->expectException(ValidationException::class);
        app(MessagingService::class)->send($candidate, $conversation, 'Pièce jointe', [$document->id]);
    }
}