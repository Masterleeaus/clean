<?php

return [
    'default_currency' => env('TITAN_MONEY_DEFAULT_CURRENCY', 'AUD'),
    'default_timezone' => env('TITAN_MONEY_DEFAULT_TIMEZONE', 'Australia/Sydney'),
    'default_tax_rate_basis_points' => (int) env('TITAN_MONEY_DEFAULT_TAX_RATE_BPS', 1000),
    'default_payment_terms_days' => (int) env('TITAN_MONEY_DEFAULT_TERMS_DAYS', 14),
    'invoice_prefix' => env('TITAN_MONEY_INVOICE_PREFIX', 'INV'),
    'reminder_offsets' => [-7, 0, 7, 14],
    'private_disk' => env('TITAN_MONEY_PRIVATE_DISK', 'local'),
    'automation' => [
        'send_on_issue' => (bool) env('TITAN_MONEY_SEND_ON_ISSUE', true),
        'agent_schedule_minutes' => (int) env('TITAN_MONEY_AGENT_SCHEDULE_MINUTES', 5),
        'outbox_batch_size' => (int) env('TITAN_MONEY_OUTBOX_BATCH_SIZE', 200),
        'delivery_batch_size' => (int) env('TITAN_MONEY_DELIVERY_BATCH_SIZE', 100),
    ],
];
