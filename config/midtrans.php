<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-YOUR_SANDBOX_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-YOUR_SANDBOX_CLIENT_KEY'),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),

    // Base API URLs
    'snap_api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'snap_js_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    'core_api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

    // SSL Verification
    'verify_ssl' => env('MIDTRANS_VERIFY_SSL', true),
    'ca_bundle' => env('MIDTRANS_CA_BUNDLE', null),
];

