<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('AI_SITE_BUILDER_ENABLED', false),
    'base_url' => rtrim((string) env('AI_SITE_BUILDER_BASE_URL', ''), '/'),
    'client_id' => (string) env('AI_SITE_BUILDER_CLIENT_ID', 'magicai'),
    'shared_secret' => (string) env('AI_SITE_BUILDER_SHARED_SECRET', ''),
    'webhook_secret' => (string) env('AI_SITE_BUILDER_WEBHOOK_SECRET', ''),
    'timeout' => (int) env('AI_SITE_BUILDER_TIMEOUT', 20),
    'permissions' => [
        'read' => 'integration.ai_site_builder.read',
        'create' => 'integration.ai_site_builder.create',
    ],
    'context_keys' => [
        'business_name',
        'business_description',
        'services',
        'service_areas',
        'brand',
        'public_contact',
        'property_summary',
        'compliance_summary',
    ],
    'context_max_bytes' => 32768,
];
