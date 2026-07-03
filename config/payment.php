<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default payment gateway
    |--------------------------------------------------------------------------
    |
    | Fallback when Admin Settings do not specify a gateway. The active gateway
    | is resolved at runtime from settings via PaymentSettingsResolver.
    |
    */
    'default' => env('PAYMENT_GATEWAY', 'razorpay'),

    /*
    |--------------------------------------------------------------------------
    | Registered payment gateways
    |--------------------------------------------------------------------------
    |
    | Map gateway slug => gateway class. Add new gateways here without changing
    | checkout business logic (Open/Closed Principle).
    |
    */
    'gateways' => [
        'razorpay' => App\Services\Payments\Gateways\RazorpayGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway runtime credentials (injected by PaymentSettingsResolver)
    |--------------------------------------------------------------------------
    */
    'credentials' => [
        'razorpay' => [
            'key_id' => null,
            'key_secret' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency fallback
    |--------------------------------------------------------------------------
    */
    'currency' => env('PAYMENT_CURRENCY', 'INR'),

];
