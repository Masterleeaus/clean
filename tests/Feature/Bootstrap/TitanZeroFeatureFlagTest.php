<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

function titanZeroFeatureFlagIssues(string $root): array
{
    $issues = [];
    $flagsPath = $root . '/app/Support/TitanZero/TitanZeroFeatureFlags.php';
    if (! is_file($flagsPath)) {
        return ['Missing TitanZeroFeatureFlags implementation.'];
    }

    require_once $flagsPath;

    $cases = [
        'disabled' => [
            'config' => ['workcore_enabled' => false, 'chatbot_enabled' => false],
            'providers' => [],
        ],
        'workcore-only' => [
            'config' => ['workcore_enabled' => true, 'chatbot_enabled' => false],
            'providers' => ['App\\Domains\\WorkCore\\WorkCoreServiceProvider'],
        ],
        'chatbot-only' => [
            'config' => ['workcore_enabled' => false, 'chatbot_enabled' => true],
            'providers' => ['App\\Extensions\\Chatbot\\System\\ChatbotServiceProvider'],
        ],
        'workcore-and-chatbot' => [
            'config' => ['workcore_enabled' => true, 'chatbot_enabled' => true],
            'providers' => [
                'App\\Domains\\WorkCore\\WorkCoreServiceProvider',
                'App\\Extensions\\Chatbot\\System\\ChatbotServiceProvider',
            ],
        ],
    ];

    foreach ($cases as $name => $case) {
        $flags = \App\Support\TitanZero\TitanZeroFeatureFlags::fromArray($case['config']);
        if ($flags->coreProviderClassNames() !== $case['providers']) {
            $issues[] = sprintf('%s provider plan mismatch.', $name);
        }
    }

    $all = \App\Support\TitanZero\TitanZeroFeatureFlags::fromArray([
        'workcore_enabled' => true,
        'chatbot_enabled' => true,
        'interaction_engine_enabled' => true,
        'extension_discovery_enabled' => true,
    ]);

    foreach (['workCoreEnabled', 'chatbotEnabled', 'interactionEngineEnabled', 'extensionDiscoveryEnabled'] as $method) {
        if ($all->{$method}() !== true) {
            $issues[] = "{$method} did not preserve its configured value.";
        }
    }
    if (! in_array('App\\Providers\\ExtensionServiceProvider', $all->coreProviderClassNames(), true)) {
        $issues[] = 'Extension discovery did not add ExtensionServiceProvider to the provider plan.';
    }

    foreach (['config/titan-zero.php', 'app/Providers/TitanZeroServiceProvider.php'] as $relative) {
        if (! is_file($root . '/' . $relative)) {
            $issues[] = "Missing staged boot file: {$relative}";
        }
    }

    $appConfig = (string) @file_get_contents($root . '/config/app.php');
    $usesImportedProvider = str_contains($appConfig, 'use App\\Providers\\TitanZeroServiceProvider;')
        && str_contains($appConfig, 'TitanZeroServiceProvider::class');
    $usesFullyQualifiedProvider = str_contains($appConfig, 'App\\Providers\\TitanZeroServiceProvider::class');
    if (! $usesImportedProvider && ! $usesFullyQualifiedProvider) {
        $issues[] = 'config/app.php does not register TitanZeroServiceProvider.';
    }
    if (str_contains($appConfig, 'App\\Domains\\WorkCore\\WorkCoreServiceProvider::class')) {
        $issues[] = 'config/app.php still registers WorkCore directly.';
    }

    $marketplaceProvider = (string) @file_get_contents($root . '/app/Domains/Marketplace/MarketplaceServiceProvider.php');
    if (str_contains($marketplaceProvider, '$this->extensionProviderRegister();')) {
        $issues[] = 'Marketplace still registers extension providers directly.';
    }

    $extensionProvider = (string) @file_get_contents($root . '/app/Providers/ExtensionServiceProvider.php');
    if (! str_contains($extensionProvider, 'resolveEnabledProviders()')) {
        $issues[] = 'ExtensionServiceProvider does not use validated provider discovery.';
    }

    $chatbotProvider = (string) @file_get_contents($root . '/app/Extensions/Chatbot/System/ChatbotServiceProvider.php');
    if (! str_contains($chatbotProvider, 'workCoreEnabled()')) {
        $issues[] = 'Chatbot WorkCore integration is not guarded by Titan Zero feature flags.';
    }

    return $issues;
}

if (class_exists(TestCase::class)) {
    final class TitanZeroFeatureFlagTest extends TestCase
    {
        public function test_core_provider_plan_supports_staged_boot_combinations(): void
        {
            self::assertSame([], titanZeroFeatureFlagIssues(dirname(__DIR__, 3)));
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $issues = titanZeroFeatureFlagIssues(dirname(__DIR__, 3));
    if ($issues !== []) {
        fwrite(STDERR, implode(PHP_EOL, $issues) . PHP_EOL);
        exit(1);
    }
    fwrite(STDOUT, "Titan Zero feature flag test passed.\n");
}
