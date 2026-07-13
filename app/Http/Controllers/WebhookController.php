<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function handle(Request $request, string $gateway, PaymentManager $payments): Response
    {
        $payments->handleWebhook($gateway, $request);

        return response('ok', 200);
    }
}
