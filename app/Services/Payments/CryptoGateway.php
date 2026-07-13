<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

class CryptoGateway implements PaymentGateway
{
    public function __construct(private readonly array $config)
    {
    }

    public function key(): string
    {
        return 'crypto';
    }

    public function displayName(): string
    {
        return 'Cryptocurrency';
    }

    public function initiate(Payment $payment): array
    {
        // Deterministic per-payment deposit reference the processor account
        // is configured to watch. Swap this block for your processor's
        // create-invoice API call; the rest of the flow is unchanged.
        $reference = strtoupper(substr(hash_hmac('sha256', $payment->uuid, (string) $this->config['api_key']), 0, 16));

        $payment->update([
            'gateway_reference' => $reference,
            'metadata' => array_merge($payment->metadata ?? [], [
                'instructions' => 'Send the exact amount and include the reference in the memo field.',
            ]),
        ]);

        return [
            'type' => 'crypto',
            'reference' => $reference,
            'amount' => number_format($payment->amount / 100, 2),
            'currency' => $payment->currency,
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret = (string) $this->config['webhook_secret'];
        $signature = (string) $request->header('X-Signature', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): array
    {
        return [
            (string) $request->input('reference'),
            (string) $request->input('status'), // confirmed|failed
            (int) $request->input('amount_minor'),
        ];
    }
}
