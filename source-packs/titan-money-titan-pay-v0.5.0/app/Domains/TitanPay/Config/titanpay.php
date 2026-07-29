<?php

return [
    'name' => 'Titan Pay',
    'private_disk' => env('TITANPAY_PRIVATE_DISK', 'local'),
    'session_ttl_minutes' => (int) env('TITANPAY_SESSION_TTL_MINUTES', 1440),
    'stripe_tolerance_seconds' => (int) env('TITANPAY_STRIPE_TOLERANCE_SECONDS', 300),
    'allowed_methods' => ['payid', 'bank_transfer', 'cash', 'paypal'],
    'allow_customer_stripe' => (bool) env('TITANPAY_ALLOW_CUSTOMER_STRIPE', false),
    'qr' => [
        'python_binary' => env('TITANPAY_QR_PYTHON_BINARY', 'python3'),
        'timeout_seconds' => (int) env('TITANPAY_QR_TIMEOUT_SECONDS', 5),
    ],
    'max_evidence_kb' => (int) env('TITANPAY_MAX_EVIDENCE_KB', 5120),

    /* Environment references are resolved through config so they survive config:cache. */
    'secrets' => [
        'TITANPAY_STRIPE' => array_filter([
            'secret_key' => env('TITANPAY_STRIPE_SECRET', env('TITANPAY_STRIPE_SECRET_KEY')),
            'publishable_key' => env('TITANPAY_STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('TITANPAY_STRIPE_WEBHOOK_SECRET'),
        ], static fn ($value): bool => $value !== null && $value !== ''),
        'TITANPAY_PAYPAL' => array_filter([
            'client_id' => env('TITANPAY_PAYPAL_CLIENT_ID'),
            'client_secret' => env('TITANPAY_PAYPAL_CLIENT_SECRET'),
            'webhook_id' => env('TITANPAY_PAYPAL_WEBHOOK_ID'),
        ], static fn ($value): bool => $value !== null && $value !== ''),
        'TITANPAY_PAYID' => array_filter([
            'payid' => env('TITANPAY_PAYID_VALUE'),
            'account_name' => env('TITANPAY_PAYID_ACCOUNT_NAME'),
        ], static fn ($value): bool => $value !== null && $value !== ''),
        'TITANPAY_BANK' => array_filter([
            'bsb' => env('TITANPAY_BANK_BSB'),
            'account_number' => env('TITANPAY_BANK_ACCOUNT_NUMBER'),
            'account_name' => env('TITANPAY_BANK_ACCOUNT_NAME'),
        ], static fn ($value): bool => $value !== null && $value !== ''),
    ],
];
