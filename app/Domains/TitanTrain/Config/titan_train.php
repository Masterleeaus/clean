<?php

declare(strict_types=1);

return [
    'enabled' => env('TITAN_TRAIN_ENABLED', true),
    'api_prefix' => env('TITAN_TRAIN_API_PREFIX', 'api/v1/titan-train'),
    'manager_roles' => ['owner', 'admin', 'manager', 'trainer'],
    'cleaner_roles' => ['cleaner', 'worker', 'member', 'learner'],
    'default_due_days' => 14,
    'pwa' => [
        'enabled' => true,
        'manifest_url' => '/chatbot-pwa/apps/titan-train.json',
        'bridge_url' => '/chatbot-pwa/apps/titan-train.js',
        'launch_url' => '/train',
    ],
];
