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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'discord' => [
        'webhook_url' => env('DISCORD_WEBHOOK_URL')
    ],

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'service_account' => env('FIREBASE_SERVICE_ACCOUNT'),
        'vapid_key' => env('FIREBASE_VAPID_KEY'),
        'service_database_inbox_url' => env('FIREBASE_SERVICE_DATABASE_INBOX_URL'),
        'service_database_checkin_url' => env('FIREBASE_SERVICE_DATABASE_CHECKIN_URL'),
    ],

    'device_iot_api' => [
        'url' => env('DEVICE_API_URL', 'https://iot.keloola.com/api/bos-device-list'),
        'url_device_device' => env('DEVICE_URL_status', 'https://iot.keloola.com/api/bos-device-status'),
        'Authorization' => env('API_BEARER_TOKEN'),
    ],

    'checking_setting' => 
    [
        'times_per_day' => env('CHECKIN_TIMES_PER_DAY', 4),
        'duration' => env('CHECKIN_DURATION', 120),
        'duration_minutes' => env('CHECKIN_DURATION_MINUTES', 2),
        'times' => env('CHECKIN_TIME', 10),
    ],

    'cuti_api' => 
    [
        'base_url' => env('CUTI_API_BASE_URL', 'https://hris.gemateknologi.com/CutiApi'),
        'token' => env('CUTI_API_TOKEN'),
    ],
    
    'setting' =>
    [
        'app_socet_url' => env("APP_SOCET_URL", 'https://keloola-bos-management.test:6001'),
        'punishment_point' => env('SETTING_PUNISHMENT_POINT', -100)
    ],

    'path' => 
    [
        'ghost_script' => env('PATH_GHOST_SCRIPT', 'gs'),
    ],

    'connection_reverb' =>
    [
        'host' => env('PUSHER_HOST', env('REVERB_HOST', 'ws.keloola.xyz')),
        'key' => env('PUSHER_APP_KEY', env('REVERB_APP_KEY',null)),
        'port' => env('PUSHER_PORT', env('REVERB_PORT', 443)),
    ],

    'google' =>
    [
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/google/oauth/callback'),
        'redirect_url_public' => env('GOOGLE_REDIRECT_URI_PUBLIC', 'http://localhost:8000/meeting/public/oauth/callback'),
        'max_description_length' => env('GOOGLE_MAX_DESCRIPTION_LENGTH', 8000),
    ],
    
    'internet_custom' =>
    [
        'end_billing_of_days' => env('END_BILLING_OF_DAYS', 5),
        'max_billing_date' => env('MAX_BILLING_DATE', 20), // Jika > 20, normalisasi ke tanggal 1
        'default_tax_rate' => env('DEFAULT_TAX_RATE', 11), // Default PPN 11%
        'tutorial_payment' => env('TUTORIAL_PAYMENT', null),
    ],
    'used_laptop_charge' =>
    [
        'expedition_charge' => env('USED_LAPTOP_EXPEDITION_CHARGE', 60000),
        'totebag_charge' => env('USED_LAPTOP_TOTE_BAG_CHARGE', 46421),
        'totebag_cover_charge' => env('USED_LAPTOP_TOTE_BAG_COVER_CHARGE', 3800),
    ],

    'keloola_pay' =>
    [
        'base_url' => env('KELOOLOA_PAY_BASE_URL', 'https://pay.keloola.xyz'),
    ],
    'n8n' => [
        'base_url' => env('N8N_BASE_URL', 'https://n8n.keloola.xyz'),
        'ktp_webhook_path' => env('N8N_KTP_WEBHOOK_PATH', '/webhook-test/e-kyc-hikari'),
    ],

    'openai' => [
        'nominal_threshold' => env('KELOOLA_OPENAI_NOMINAL_TRAHOLD', 100000000),
    ],
];
