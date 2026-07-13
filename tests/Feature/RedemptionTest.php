<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use App\Services\LinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function makeLink(string $destination = 'https://example.com/secret'): array
    {
        $user = User::factory()->create();

        return app(LinkService::class)->create($destination, $user);
    }

    public function test_first_visit_redeems_and_redirects(): void
    {
        [, $token] = $this->makeLink('https://example.com/target');

        $response = $this->get("/r/{$token}");

        $response->assertRedirect('https://example.com/target');
        $this->assertDatabaseHas('links', ['status' => Link::STATUS_REDEEMED]);
    }

    public function test_second_visit_shows_already_redeemed(): void
    {
        [, $token] = $this->makeLink();

        $this->get("/r/{$token}");
        $second = $this->get("/r/{$token}");

        $second->assertStatus(410);
        $second->assertSee('Already redeemed');
    }

    public function test_redemption_is_atomic_only_one_winner(): void
    {
        [$link, $token] = $this->makeLink();

        // Simulate the losing side of a race: once the row is redeemed, the
        // guarded UPDATE must affect zero rows for every later attempt.
        $service = app(LinkService::class);

        $first = $service->redeem($token, '10.0.0.1');
        $second = $service->redeem($token, '10.0.0.2');

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, Link::where('status', Link::STATUS_REDEEMED)->count());
    }

    public function test_expired_link_shows_expired_page(): void
    {
        [$link, $token] = $this->makeLink();
        $link->forceFill(['expires_at' => now()->subMinute()])->save();

        $response = $this->get("/r/{$token}");

        $response->assertStatus(410);
        $response->assertSee('expired');
    }

    public function test_unknown_token_shows_invalid_page(): void
    {
        $response = $this->get('/r/'.str_repeat('a', 64));

        $response->assertStatus(404);
        $response->assertSee('Invalid link');
    }

    public function test_malformed_token_never_reaches_controller(): void
    {
        $this->get('/r/not-a-token')->assertStatus(404);
    }
}
