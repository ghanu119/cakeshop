<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Messaging abstraction (future-ready)
    |--------------------------------------------------------------------------
    |
    | All outbound OTP / notification messaging goes through a provider-agnostic
    | gateway. Swap providers (or add SMS) later by adding a driver class and
    | changing MESSAGING_DRIVER -- callers never change.
    |
    */

    'messaging' => [
        'driver' => env('MESSAGING_DRIVER', 'whatsapp_cloud'),
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v21.0'),
        'phone_number_id' => env('WHATSAPP_NUMBER_ID'),
        'business_id' => env('WHATSAPP_BUSINESS_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '91'),

        'otp_template' => env('WHATSAPP_OTP_TEMPLATE', 'login_otp'),
        'otp_template_lang' => env('WHATSAPP_OTP_TEMPLATE_LANG', 'en_US'),

        // Number of body variables the OTP template expects (the code is repeated
        // across each). Dedicated authentication templates use 1.
        'otp_body_params' => env('WHATSAPP_OTP_BODY_PARAMS', 1),
        // Append the WhatsApp authentication copy-code URL button.
        'otp_copy_code_button' => env('WHATSAPP_OTP_COPY_CODE_BUTTON', false),
        'order_template' => env('WHATSAPP_ORDER_TEMPLATE', 'order_update'),
        'order_template_lang' => env('WHATSAPP_ORDER_TEMPLATE_LANG', 'en_US'),

        // Test mode: all sends are redirected to test_number, but OTPs are still
        // keyed to / verified against the number the user actually entered.
        'test_mode' => env('WHATSAPP_TEST_MODE', false),
        'test_number' => env('WHATSAPP_TEST_TO_NUMBER'),

        // Admin WhatsApp alerts: if blank, admin WhatsApp notifications are disabled.
        'admin_number' => env('WHATSAPP_ADMIN_NUMBER'),

        // When true, order-status notifications are also sent to the customer on WhatsApp.
        'customer_order_notifications' => env('WHATSAPP_CUSTOMER_ORDER_NOTIFICATIONS', false),
    ],

];
