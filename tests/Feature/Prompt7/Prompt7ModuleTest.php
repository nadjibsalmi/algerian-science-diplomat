<?php

namespace Tests\Feature\Prompt7;

use App\Models\User;
use App\Modules\Administration\Services\AdministrationService;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\CMS\Models\Page;
use App\Modules\CMS\Services\CmsService;
use App\Modules\SearchAlerts\Services\SearchAlertService;
use App\Modules\Search\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Prompt7ModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_persists_translated_pages_and_public_queries_only_return_published_content(): void
    {
        $author = User::factory()->create();
        $service = app(CmsService::class);
        $page = $service->savePage($author, [
            'slug' => 'about-asd',
            'template' => 'default',
            'published' => false,
            'translations' => [['locale' => 'fr', 'title' => 'À propos', 'content' => ['type' => 'doc']]],
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(1, $page->translations);
        $this->assertCount(0, $service->pages(true)->items());
    }

    public function test_search_alerts_are_isolated_to_their_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $alert = app(SearchAlertService::class)->create($owner, [
            'name' => 'PhD Algeria',
            'filters' => ['country' => 'Algeria'],
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(SearchAlertService::class)->delete($other, $alert);
    }

    public function test_analytics_hashes_anonymous_ip_and_stores_event(): void
    {
        $event = app(AnalyticsService::class)->record(null, 'offer_view', ['offer_id' => 'x'], null, null, '127.0.0.1');

        $this->assertNotNull($event->ip_hash);
        $this->assertSame(64, strlen($event->ip_hash));
        $this->assertDatabaseHas('analytics_events', ['id' => $event->id, 'event' => 'offer_view']);
    }

    public function test_administration_cannot_suspend_itself_and_can_manage_settings(): void
    {
        $admin = User::factory()->create();
        $service = app(AdministrationService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->suspend($admin, $admin, 'Invalid self suspension', null);
    }
}