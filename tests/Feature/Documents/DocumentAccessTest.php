<?php

namespace Tests\Feature\Documents;

use App\Models\User;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_share_token_is_hashed_and_resolves_only_for_clean_documents(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $document = Document::create([
            'user_id' => $user->id,
            'type' => 'cv',
            'name' => 'CV',
            'original_filename' => 'cv.pdf',
            'path' => 'candidates/'.$user->id.'/documents/cv.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10,
            'status' => Document::STATUS_CLEAN,
        ]);

        $token = app(DocumentService::class)->share($user, $document);

        $this->assertNotSame($token, $document->fresh()->share_token);
        $this->assertSame($document->id, app(DocumentService::class)->resolveShareToken($token)->id);
    }

    public function test_infected_documents_cannot_be_shared(): void
    {
        $user = User::factory()->create();
        $document = Document::create([
            'user_id' => $user->id,
            'type' => 'cv',
            'name' => 'CV',
            'original_filename' => 'cv.pdf',
            'path' => 'infected.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10,
            'status' => Document::STATUS_INFECTED,
        ]);

        $this->expectException(ValidationException::class);
        app(DocumentService::class)->share($user, $document);
    }
}