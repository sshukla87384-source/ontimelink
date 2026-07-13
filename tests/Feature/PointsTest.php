<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientPointsException;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_can_never_go_negative(): void
    {
        $user = User::factory()->create(['points_balance' => 1]);
        $points = app(PointService::class);

        $points->debit($user, 1, PointTransaction::TYPE_SPEND, 'Link generated');

        $this->expectException(InsufficientPointsException::class);
        $points->debit($user, 1, PointTransaction::TYPE_SPEND, 'Link generated');
    }

    public function test_every_movement_is_ledgered_with_running_balance(): void
    {
        $user = User::factory()->create(['points_balance' => 0]);
        $points = app(PointService::class);

        $points->credit($user, 10, PointTransaction::TYPE_BONUS, 'Signup bonus');
        $points->debit($user, 3, PointTransaction::TYPE_SPEND, 'Links generated');

        $this->assertSame(7, $user->fresh()->points_balance);
        $this->assertDatabaseHas('point_transactions', ['amount' => 10, 'balance_after' => 10]);
        $this->assertDatabaseHas('point_transactions', ['amount' => -3, 'balance_after' => 7]);
    }

    public function test_generating_a_link_costs_one_point(): void
    {
        $user = User::factory()->create(['points_balance' => 2]);

        $this->actingAs($user)
            ->post('/new', ['destination' => 'https://example.com/a'])
            ->assertSuccessful();

        $this->assertSame(1, $user->fresh()->points_balance);
    }

    public function test_out_of_points_redirects_to_wallet(): void
    {
        $user = User::factory()->withoutPoints()->create();

        $this->actingAs($user)
            ->post('/new', ['destination' => 'https://example.com/a'])
            ->assertRedirect(route('wallet.index'));
    }
}
