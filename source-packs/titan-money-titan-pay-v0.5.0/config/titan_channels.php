<?php

return [
    'receipt_secret' => env('TITAN_CHANNELS_RECEIPT_SECRET'),
    'receipt_tolerance_seconds' => (int) env('TITAN_CHANNELS_RECEIPT_TOLERANCE_SECONDS', 300),
    'quiet_hours' => [
        'start' => env('TITAN_CHANNELS_QUIET_HOURS_START', '20:00'),
        'end' => env('TITAN_CHANNELS_QUIET_HOURS_END', '08:00'),
    ],
    'transports' => [
        'sms' => [
            'provider' => env('TITAN_CHANNELS_SMS_PROVIDER', 'webhook'),
            'url' => env('TITAN_CHANNELS_SMS_WEBHOOK_URL'),
            'token' => env('TITAN_CHANNELS_SMS_WEBHOOK_TOKEN'),
            'timeout_seconds' => 10,
        ],
        'voice' => [
            'provider' => env('TITAN_CHANNELS_VOICE_PROVIDER', 'webhook'),
            'url' => env('TITAN_CHANNELS_VOICE_WEBHOOK_URL'),
            'token' => env('TITAN_CHANNELS_VOICE_WEBHOOK_TOKEN'),
            'timeout_seconds' => 15,
        ],
        'whatsapp' => [
            'provider' => env('TITAN_CHANNELS_WHATSAPP_PROVIDER', 'webhook'),
            'url' => env('TITAN_CHANNELS_WHATSAPP_WEBHOOK_URL'),
            'token' => env('TITAN_CHANNELS_WHATSAPP_WEBHOOK_TOKEN'),
            'timeout_seconds' => 10,
        ],
    ],
];
