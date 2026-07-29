<?php

return [
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | AI secrets are never stored in config or environment variables.
    | A company stores only a Titan Vault credential reference in settings.ai.
    */
    'ai' => [
        'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 30),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 700),
        'temperature' => (float) env('AI_TEMPERATURE', 0.4),
        'providers' => [
            'openai' => [
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'allowed_hosts' => ['api.openai.com'],
                'default_model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
            ],
            'openrouter' => [
                'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
                'allowed_hosts' => ['openrouter.ai'],
                'default_model' => env('OPENROUTER_MODEL', 'openai/gpt-4.1-mini'),
            ],
            'gemini' => [
                'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
                'allowed_hosts' => ['generativelanguage.googleapis.com'],
                'default_model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
            ],
        ],
    ],
];
