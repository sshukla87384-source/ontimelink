<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // public identifier for dashboards
            // SHA-256 of the redemption token. The raw token is shown once to
            // the creator and never stored, so a database leak cannot be used
            // to redeem links.
            $table->char('token_hash', 64)->unique();
            // Destination URL encrypted with AES-256 (Laravel Crypt). Never
            // stored or logged in plain text.
            $table->text('destination_encrypted');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_hash', 64)->nullable()->index(); // hashed IP for guest quota
            $table->string('status', 20)->default('active'); // active|redeemed|expired|disabled
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->string('redeemed_ip_hash', 64)->nullable();
            $table->string('label', 120)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);      // dashboard filters
            $table->index(['status', 'expires_at']);   // scheduler sweep
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
