<?php

declare(strict_types=1);

return [
    'workcore_enabled' => env('TITAN_ZERO_WORKCORE_ENABLED', true),
    'chatbot_enabled' => env('TITAN_ZERO_CHATBOT_ENABLED', false),
    'interaction_engine_enabled' => env('TITAN_ZERO_INTERACTION_ENGINE_ENABLED', false),
    'extension_discovery_enabled' => env('TITAN_ZERO_EXTENSION_DISCOVERY_ENABLED', false),
];
