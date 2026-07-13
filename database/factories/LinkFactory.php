<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        // Raw token is discarded here on purpose: tests that need to redeem
        // should use LinkService::create(), which returns the plain token.
        $token = bin2hex(random_bytes(32));

        return [
            'user_id' => User::factory(),
            'token_hash' => hash('sha256', $token),
            'destination_encrypted' => Crypt::encryptString(fake()->url()),
            'status' => Link::STATUS_ACTIVE,
            'label' => fake()->boolean(60) ? fake()->words(3, true) : null,
        ];
    }

    public function redeemed(): static
    {
        return $this->state(fn () => [
            'status' => Link::STATUS_REDEEMED,
            'redeemed_at' => now()->subHour(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}
