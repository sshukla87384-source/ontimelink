<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_exactly_one_free_link(): void
    {
        $this->post('/new', ['destination' => 'https://example.com/one'])
            ->assertSuccessful();

        $this->post('/new', ['destination' => 'https://example.com/two'])
            ->assertRedirect(route('register'));

        $this->assertDatabaseCount('links', 1);
    }
}
