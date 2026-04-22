<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Courier Service (J&T Express / LBC)
    |--------------------------------------------------------------------------
    | Set USE_REAL_COURIER=true in .env and provide credentials to go live.
    */
    'courier' => [
        'use_real'       => env('USE_REAL_COURIER', false),
        'provider'       => env('COURIER_PROVIDER', 'jnt'),        // jnt | lbc | ninjavan
        'api_key'        => env('COURIER_API_KEY', ''),
        'api_url'        => env('COURIER_API_URL', 'https://jtexpressph.com/api'),
        'sender_phone'   => env('COURIER_SENDER_PHONE', ''),
        'sender_address' => env('COURIER_SENDER_ADDRESS', 'Mindanao, Philippines'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayMongo (GCash, Maya, Cards)
    |--------------------------------------------------------------------------
    | Get keys from: https://dashboard.paymongo.com/developers
    | Set USE_REAL_PAYMENT=true to activate.
    */
    'paymongo' => [
        'use_real'       => env('USE_REAL_PAYMENT', false),
        'public_key'     => env('PAYMONGO_PUBLIC_KEY', ''),
        'secret_key'     => env('PAYMONGO_SECRET_KEY', ''),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio (SMS)
    |--------------------------------------------------------------------------
    | Get credentials from: https://console.twilio.com/
    | Set USE_REAL_NOTIFICATIONS=true to activate.
    */
    'twilio' => [
        'use_real'     => env('USE_REAL_NOTIFICATIONS', false),
        'sid'          => env('TWILIO_SID', ''),
        'auth_token'   => env('TWILIO_AUTH_TOKEN', ''),
        'from_number'  => env('TWILIO_FROM_NUMBER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailgun (Transactional Email)
    |--------------------------------------------------------------------------
    | Get credentials from: https://app.mailgun.com/
    | Set USE_REAL_NOTIFICATIONS=true to activate.
    */
    'mailgun' => [
        'use_real' => env('USE_REAL_NOTIFICATIONS', false),
        'domain'   => env('MAILGUN_DOMAIN', ''),
        'secret'   => env('MAILGUN_SECRET', ''),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

];