<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment gateways
    |--------------------------------------------------------------------------
    | Only cryptocurrency and WalletPay are supported. New gateways are added
    | by writing a class implementing App\Services\Payments\PaymentGateway
    | and registering it here - no other code changes are required.
    */

    'default' => 'crypto',

    'gateways' => [
        'crypto' => [
            'driver' => \App\Services\Payments\CryptoGateway::class,
            'enabled' => (bool) env('PAYMENT_CRYPTO_ENABLED', true),
            'api_key' => env('PAYMENT_CRYPTO_API_KEY'),
            'webhook_secret' => env('PAYMENT_CRYPTO_WEBHOOK_SECRET'),
        ],
        'walletpay' => [
            'driver' => \App\Services\Payments\WalletPayGateway::class,
            'enabled' => (bool) env('PAYMENT_WALLETPAY_ENABLED', true),
            'merchant_id' => env('PAYMENT_WALLETPAY_MERCHANT_ID'),
            'api_key' => env('PAYMENT_WALLETPAY_API_KEY'),
            'webhook_secret' => env('PAYMENT_WALLETPAY_WEBHOOK_SECRET'),
        ],
    ],
];
