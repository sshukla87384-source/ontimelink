<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the initial administrator from environment variables so no
 * credentials are ever committed to the repository.
 *
 * Set ADMIN_EMAIL (and optionally ADMIN_NAME / ADMIN_PASSWORD) in .env
 * before running `php artisan db:seed`. When ADMIN_PASSWORD is omitted a
 * random one is generated and printed ONCE to the console.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');

        if (! $email) {
            $this->command?->warn('ADMIN_EMAIL not set - skipping admin seeding.');

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists - nothing to do.");

            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(20);

        User::create([
            'name' => env('ADMIN_NAME', 'Administrator'),
            'email' => $email,
            'password' => $password,
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->command?->info("Admin account created for {$email}.");

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Generated admin password (store it now, it is not saved): {$password}");
        }
    }
}
