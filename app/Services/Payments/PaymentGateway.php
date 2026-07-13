<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Contract every payment gateway implements. Adding a gateway means writing
 * one class and one config entry - controllers and services never change.
 */
interface PaymentGateway
{
    public function key(): string;

    public function displayName(): string;

    /**
     * Begin a checkout for the given pending Payment.
     * Returns instructions to render (redirect URL, address, QR payload...).
     */
    public function initiate(Payment $payment): array;

    /**
     * Verify a webhook's authenticity. MUST use a constant-time signature
     * comparison and MUST NOT trust any amount/status field before verifying.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Extract [gatewayReference, status, paidMinorUnits] from a verified webhook.
     */
    public function parseWebhook(Request $request): array;
}
