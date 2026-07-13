<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 30); // crypto|walletpay
            $table->string('gateway_reference')->nullable()->index(); // provider txn id
            $table->unsignedBigInteger('amount'); // minor units
            $table->char('currency', 3)->default('USD');
            $table->unsignedBigInteger('points_purchased')->default(0);
            $table->string('status', 20)->default('pending'); // pending|confirmed|failed|expired
            $table->json('metadata')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->unique(['gateway', 'gateway_reference']); // webhook idempotency
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
