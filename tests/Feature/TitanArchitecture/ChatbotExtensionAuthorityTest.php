<?php

declare(strict_types=1);

namespace Tests\Feature\TitanArchitecture;

use App\Domains\Marketplace\MarketplaceServiceProvider;
use App\Extensions\Chatbot\System\ChatbotServiceProvider;
use Tests\TestCase;

final class ChatbotExtensionAuthorityTest extends TestCase
{
    public function test_marketplace_registers_only_the_canonical_chatbot_provider(): void
    {
        self::assertArrayHasKey('chatbot', MarketplaceServiceProvider::$extensionProviders);
        self::assertSame(
            ChatbotServiceProvider::class,
            MarketplaceServiceProvider::$extensionProviders['chatbot'],
        );

        self::assertNotContains(
            'App\\Extensions\\TitanZeroChatbot\\System\\ChatbotServiceProvider',
            MarketplaceServiceProvider::$extensionProviders,
            true,
        );
    }

    public function test_extension_authority_configuration_points_to_the_canonical_tree(): void
    {
        $config = require base_path('app/Extensions/Chatbot/config/titan_project_architecture.php');
        $authority = $config['chatbot_extension'];

        self::assertSame('chatbot', $authority['slug']);
        self::assertSame('app/Extensions/Chatbot', $authority['authoritative_path']);
        self::assertSame(ChatbotServiceProvider::class, $authority['authoritative_provider']);
        self::assertSame('app/Extensions/TitanZeroChatbot', $authority['legacy_path']);
        self::assertFalse($authority['legacy_boot_enabled']);
    }

    public function test_legacy_snapshot_cannot_present_itself_as_an_installable_extension(): void
    {
        $legacyRoot = base_path('app/Extensions/TitanZeroChatbot');
        $manifest = json_decode(
            (string) file_get_contents($legacyRoot.'/extension.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $provider = (string) file_get_contents($legacyRoot.'/System/ChatbotServiceProvider.php');

        self::assertSame('legacy-disabled', $manifest['type']);
        self::assertFalse($manifest['enabled']);
        self::assertFalse($manifest['bootable']);
        self::assertNull($manifest['provider']);
        self::assertSame('app/Extensions/Chatbot', $manifest['authoritative_extension']);

        self::assertStringContainsString(
            'namespace App\\Extensions\\TitanZeroChatbot\\System;',
            $provider,
        );
        self::assertStringNotContainsString(
            'namespace App\\Extensions\\Chatbot\\System;',
            $provider,
        );
        self::assertStringContainsString('throw new LogicException', $provider);
        self::assertStringNotContainsString('loadMigrationsFrom', $provider);
        self::assertStringNotContainsString('registerRoutes', $provider);
    }

    public function test_composer_namespace_resolution_favours_the_canonical_tree(): void
    {
        $composer = json_decode(
            (string) file_get_contents(base_path('composer.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('app/', $composer['autoload']['psr-4']['App\\']);
        self::assertFileExists(base_path('app/Extensions/Chatbot/System/ChatbotServiceProvider.php'));
        self::assertFileExists(base_path('app/Extensions/Chatbot/extension-authority.json'));
    }
}
