<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: confirms the public homepage / offers listing responds
 * successfully to an unauthenticated visitor — the most basic end-to-end
 * check for the only production-ready page in the current build.
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_offers_listing_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_offers_route_also_returns_a_successful_response(): void
    {
        $response = $this->get('/offers');

        $response->assertStatus(200);
    }
}
