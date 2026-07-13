<?php

namespace App\Services;

use App\Exceptions\InsufficientPointsException;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Every balance change happens inside a transaction with a row lock and is
 * written to the point_transactions ledger. Balances can never go negative:
 * debits re-check the locked balance before committing.
 */
class PointService
{
    public function credit(
        User $user,
        int $amount,
        string $type,
        string $description,
        ?Model $reference = null,
        ?int $performedBy = null,
    ): PointTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $reference, $performedBy) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $locked->points_balance += $amount;
            $locked->save();
            $user->points_balance = $locked->points_balance;

            return $this->record($locked, $type, $amount, $description, $reference, $performedBy);
        });
    }

    public function debit(
        User $user,
        int $amount,
        string $type,
        string $description,
        ?Model $reference = null,
        ?int $performedBy = null,
    ): PointTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Debit amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $reference, $performedBy) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->points_balance < $amount) {
                throw new InsufficientPointsException(
                    "Insufficient points: needed {$amount}, available {$locked->points_balance}."
                );
            }

            $locked->points_balance -= $amount;
            $locked->save();
            $user->points_balance = $locked->points_balance;

            return $this->record($locked, $type, -$amount, $description, $reference, $performedBy);
        });
    }

    private function record(
        User $user,
        string $type,
        int $signedAmount,
        string $description,
        ?Model $reference,
        ?int $performedBy,
    ): PointTransaction {
        return PointTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $signedAmount,
            'balance_after' => $user->points_balance,
            'description' => $description,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'performed_by' => $performedBy,
        ]);
    }
}
