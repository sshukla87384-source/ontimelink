<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PointService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentManager
{
    /** @var array<string, PaymentGateway> */
    private array $gateways = [];

    public function __construct(
        private readonly PointService $points,
        private readonly WalletService $wallet,
        private readonly AuditService $audit,
    ) {
        foreach (config('payments.gateways', []) as $key => $cfg) {
            if (! ($cfg['enabled'] ?? false)) {
                continue;
            }
            $this->gateways[$key] = new $cfg['driver']($cfg);
        }
    }

    /** @return array<string, PaymentGateway> */
    public function enabled(): array
    {
        return $this->gateways;
    }

    public function gateway(string $key): PaymentGateway
    {
        return $this->gateways[$key]
            ?? throw new \InvalidArgumentException("Payment gateway [$key] is not enabled.");
    }

    public function createPointsPurchase(User $user, string $gatewayKey, int $pointsQty): array
    {
        $gateway = $this->gateway($gatewayKey);
        $amountMinor = $pointsQty * 10; // $0.10 per point; tune via Settings if needed

        $payment = Payment::create([
            'user_id' => $user->id,
            'gateway' => $gateway->key(),
            'amount' => $amountMinor,
            'currency' => 'USD',
            'points_purchased' => $pointsQty,
            'status' => Payment::STATUS_PENDING,
        ]);

        $instructions = $gateway->initiate($payment);

        $this->audit->log('payment.initiated', 'activity', $payment, ['gateway' => $gatewayKey]);

        return [$payment->fresh(), $instructions];
    }

    /**
     * Handle a gateway webhook. Signature is verified before anything is
     * trusted; confirmation is idempotent via a locked status transition.
     */
    public function handleWebhook(string $gatewayKey, Request $request): void
    {
        $gateway = $this->gateway($gatewayKey);

        abort_unless($gateway->verifyWebhook($request), 403, 'Invalid webhook signature.');

        [$reference, $status, $paidMinor] = $gateway->parseWebhook($request);

        DB::transaction(function () use ($gateway, $reference, $status, $paidMinor) {
            $payment = Payment::where('gateway', $gateway->key())
                ->where('gateway_reference', $reference)
                ->lockForUpdate()
                ->first();

            if ($payment === null || $payment->status !== Payment::STATUS_PENDING) {
                return; // unknown or already processed - safe no-op
            }

            if ($status !== 'confirmed' || $paidMinor < $payment->amount) {
                $payment->update(['status' => Payment::STATUS_FAILED]);
                $this->audit->log('payment.failed', 'security', $payment, ['paid' => $paidMinor]);

                return;
            }

            $payment->update(['status' => Payment::STATUS_CONFIRMED, 'confirmed_at' => now()]);

            // Ledger the money in, then the conversion out - full audit trail.
            $walletModel = $payment->user->wallet;
            $this->wallet->credit($walletModel, $payment->amount, 'deposit',
                "Deposit via {$gateway->displayName()}", 'pay-in-'.$payment->uuid, $payment, allowFrozen: true);
            $this->wallet->debit($walletModel, $payment->amount, 'purchase',
                "Purchased {$payment->points_purchased} points", 'pay-out-'.$payment->uuid, $payment);

            $this->points->credit($payment->user, $payment->points_purchased,
                PointTransaction::TYPE_EARN, 'Points purchase', $payment);

            $this->audit->log('payment.confirmed', 'activity', $payment, userId: $payment->user_id);
        });
    }
}
