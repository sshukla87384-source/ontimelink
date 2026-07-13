<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Store money as integer minor units (cents) - never floats.
            $table->unsignedBigInteger('balance')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->boolean('is_frozen')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // deposit|purchase|admin_adjustment|refund
            $table->bigInteger('amount'); // minor units; positive credit, negative debit
            $table->unsignedBigInteger('balance_after');
            $table->string('reference_id', 64)->unique(); // idempotency key
            $table->string('description');
            $table->nullableMorphs('related');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
