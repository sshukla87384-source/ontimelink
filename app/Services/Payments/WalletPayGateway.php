<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

class WalletPayGateway implements PaymentGateway
{
    public function __construct(private readonly array $config)
    {
    }

    public function key(): string
    {
        return 'walletpay';
    }

    public function displayName(): string
    {
        return 'WalletPay';
    }

    public function initiate(Payment $payment): array
    {
        $reference = 'WP-'.strtoupper(substr(hash_hmac('sha256', $payment->uuid, (string) $this->config['api_key']), 0, 14));

        $payment->update(['gateway_reference' => $reference]);

        return [
            'type' => 'walletpay',
            'reference' => $reference,
            'merchant_id' => (string) $this->config['merchant_id'],
            'amount' => number_format($payment->amount / 100, 2),
            'currency' => $payment->currency,
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret = (string) $this->config['webhook_secret'];
        $signature = (string) $request->header('X-WalletPay-Signature', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): array
    {
        return [
            (string) $request->input('order_reference'),
            (string) $request->input('state'),
            (int) $request->input('amount_minor'),
        ];
    }
}
