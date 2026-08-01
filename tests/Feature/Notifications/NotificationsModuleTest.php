<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_preferences_are_upserted_per_user_and_type(): void
    {
        $user = User::factory()->create();
        $service = app(NotificationService::class);

        $service->upsertPreference($user, [
            'notification_type' => 'new_message',
            'in_app' => true,
            'email' => false,
            'push' => false,
        ]);
        $service->upsertPreference($user, [
            'notification_type' => 'new_message',
            'in_app' => false,
            'email' => true,
            'push' => true,
        ]);

        $this->assertDatabaseCount('notification_preferences', 1);
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'notification_type' => 'new_message',
            'in_app' => 0,
            'email' => 1,
            'push' => 1,
        ]);
    }

    public function test_search_alerts_are_owned_by_their_creator(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $alert = app(NotificationService::class)->createAlert($owner, [
            'name' => 'Offres IA',
            'filters' => ['research_field' => 'AI'],
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(NotificationService::class)->deleteAlert($other, $alert);
    }
}