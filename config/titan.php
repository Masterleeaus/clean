<?php

declare(strict_types=1);

return [
    'tenancy' => [
        'header' => env('TITAN_COMPANY_HEADER', 'X-Titan-Company'),
        'session_key' => env('TITAN_COMPANY_SESSION_KEY', 'titan_company_id'),
    ],
    'extensions' => [
        'path' => app_path('Extensions'),
    ],
    'private_disk' => env('TITAN_PRIVATE_DISK', 'local'),
    'host_capabilities' => [
        'identity',
        'companies',
        'memberships',
        'permissions',
        'active-company-context',
        'queues',
        'audit',
        'titan-vault',
        'extension-registry',
        'capability-registry',
        'private-export-store',
    ],
];
