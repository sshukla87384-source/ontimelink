<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            // Unique: a user can only ever be referred once - blocks duplicate rewards.
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending|rewarded|rejected
            $table->unsignedInteger('referrer_points')->default(0);
            $table->unsignedInteger('referred_points')->default(0);
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
