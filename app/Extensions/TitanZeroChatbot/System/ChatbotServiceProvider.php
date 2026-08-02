<?php

declare(strict_types=1);

namespace App\Extensions\TitanZeroChatbot\System;

use Illuminate\Support\ServiceProvider;
use LogicException;

/**
 * Non-bootable compatibility guard for the retired duplicate chatbot tree.
 *
 * The authoritative extension provider is:
 * App\Extensions\Chatbot\System\ChatbotServiceProvider
 *
 * This class deliberately registers no routes, migrations, views, assets,
 * policies, events, commands, AI providers, WorkCore services or PWA runtime.
 */
final class ChatbotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        throw new LogicException(
            'app/Extensions/TitanZeroChatbot is a disabled compatibility snapshot. '.
            'Register App\\Extensions\\Chatbot\\System\\ChatbotServiceProvider instead.'
        );
    }
}
