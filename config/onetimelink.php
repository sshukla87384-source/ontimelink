<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business rules
    |--------------------------------------------------------------------------
    | All tunables live here so behaviour can be changed without touching
    | code. Values are read once and cached with `php artisan config:cache`.
    */

    'points' => [
        'signup_bonus' => (int) env('POINTS_SIGNUP_BONUS', 10),
        'referrer_reward' => (int) env('POINTS_REFERRER_REWARD', 10),
        'referred_bonus' => (int) env('POINTS_REFERRED_BONUS', 20),
        'cost_per_link' => (int) env('POINTS_COST_PER_LINK', 1),
    ],

    'guest' => [
        'free_links' => (int) env('GUEST_FREE_LINKS', 1),
    ],

    'links' => [
        'bulk_max' => (int) env('BULK_MAX_LINKS', 100),
        // Raw token length in bytes; hex-encoded to twice this length.
        'token_bytes' => 32,
        'default_expiry_days' => null, // null = never expires until redeemed
    ],
];
