<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * All amounts are integer minor units (cents). Every mutation locks the
 * wallet row, checks freeze state and balance, and appends to the ledger
 * with a unique reference_id, so replayed webhooks cannot double-credit.
 */
class WalletService
{
    public function credit(
        Wallet $wallet,
        int $amount,
        string $type,
        string $description,
        ?string $referenceId = null,
        ?Model $reference = null,
        ?int $performedBy = null,
        bool $allowFrozen = false,
    ): WalletTransaction {
        return $this->apply($wallet, abs($amount), $type, $description, $referenceId, $reference, $performedBy, $allowFrozen);
    }

    public function debit(
        Wallet $wallet,
        int $amount,
        string $type,
        string $description,
        ?string $referenceId = null,
        ?Model $reference = null,
        ?int $performedBy = null,
    ): WalletTransaction {
        return $this->apply($wallet, -abs($amount), $type, $description, $referenceId, $reference, $performedBy, false);
    }

    public function setFrozen(Wallet $wallet, bool $frozen, ?int $performedBy = null): void
    {
        $wallet->forceFill(['is_frozen' => $frozen])->save();

        app(AuditService::class)->log(
            $frozen ? 'wallet.frozen' : 'wallet.unfrozen',
            'admin',
            $wallet,
            userId: $performedBy,
        );
    }

    private function apply(
        Wallet $wallet,
        int $signedAmount,
        string $type,
        string $description,
        ?string $referenceId,
        ?Model $reference,
        ?int $performedBy,
        bool $allowFrozen,
    ): WalletTransaction {
        if ($signedAmount === 0) {
            throw new \InvalidArgumentException('Amount must be non-zero.');
        }

        $referenceId ??= (string) Str::uuid7();

        return DB::transaction(function () use ($wallet, $signedAmount, $type, $description, $referenceId, $reference, $performedBy, $allowFrozen) {
            // Idempotency: a repeated reference (e.g. webhook retry) is a no-op.
            if ($existing = WalletTransaction::where('reference_id', $referenceId)->first()) {
                return $existing;
            }

            $locked = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ($locked->is_frozen && ! $allowFrozen) {
                throw new WalletException('Wallet is frozen.');
            }

            if ($signedAmount < 0 && $locked->balance < abs($signedAmount)) {
                throw new WalletException('Insufficient wallet balance.');
            }

            $locked->balance += $signedAmount;
            $locked->save();
            $wallet->balance = $locked->balance;

            return WalletTransaction::create([
                'wallet_id' => $locked->id,
                'type' => $type,
                'amount' => $signedAmount,
                'balance_after' => $locked->balance,
                'reference_id' => $referenceId,
                'description' => $description,
                'related_type' => $reference?->getMorphClass(),
                'related_id' => $reference?->getKey(),
                'performed_by' => $performedBy,
            ]);
        });
    }
}
