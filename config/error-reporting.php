<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Critical Error Email Reporting
    |--------------------------------------------------------------------------
    |
    | Sends email alerts for server and database critical errors in production.
    | Recipient is env-only so alerts work when the database is unavailable.
    |
    */

    'enabled' => env('APP_ENV') === 'production',

    'recipient' => env('ERROR_REPORT_EMAIL'),

    'throttle_minutes' => (int) env('ERROR_REPORT_THROTTLE_MINUTES', 15),

];
