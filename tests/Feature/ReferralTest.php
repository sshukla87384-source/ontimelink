<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_reward_is_paid_once_even_if_triggered_twice(): void
    {
        $referrer = User::factory()->create(['points_balance' => 0]);
        $referred = User::factory()->unverified()->create([
            'points_balance' => 0,
            'referred_by' => $referrer->id,
        ]);

        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        $service = app(ReferralService::class);
        $service->reward($referred);
        $service->reward($referred); // idempotent - second call is a no-op

        $expected = config('onetimelink.points.referrer_bonus');
        $this->assertSame($expected, $referrer->fresh()->points_balance);
        $this->assertSame(1, Referral::where('status', Referral::STATUS_REWARDED)->count());
    }

    public function test_self_referral_is_never_attached(): void
    {
        $user = User::factory()->create();

        app(ReferralService::class)->attach($user, $user->referral_code, '10.9.9.9');

        $this->assertSame(0, Referral::count());
    }
}
