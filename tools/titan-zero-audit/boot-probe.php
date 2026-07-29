<?php

declare(strict_types=1);

use App\Domains\Marketplace\MarketplaceServiceProvider;
use App\Domains\WorkCore\WorkCoreServiceProvider;
use App\Extensions\AIAgent\System\AIAgentServiceProvider;
use App\Extensions\Chatbot\System\ChatbotServiceProvider;
use App\Extensions\FocusMode\System\FocusModeServiceProvider;
use App\Support\TitanZero\TitanZeroFeatureFlags;
use Illuminate\Contracts\Console\Kernel;

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

/** @var Illuminate\Foundation\Application $app */
$app = require $root . '/bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

/** @var TitanZeroFeatureFlags $flags */
$flags = $app->make(TitanZeroFeatureFlags::class);
$loaded = $app->getLoadedProviders();
$enabledExtensions = array_values((array) config('titan-zero.extensions.enabled', []));

$isLoaded = static fn (string $provider): bool => ($loaded[$provider] ?? false) === true;
$issues = [];
$expectFocusMode = $flags->extensionDiscoveryEnabled() && in_array('focus-mode', $enabledExtensions, true);

$expectations = [
    WorkCoreServiceProvider::class => $flags->workCoreEnabled(),
    ChatbotServiceProvider::class => $flags->chatbotEnabled(),
    MarketplaceServiceProvider::class => true,
    FocusModeServiceProvider::class => $expectFocusMode,
];

foreach ($expectations as $provider => $expected) {
    $actual = $isLoaded($provider);
    if ($actual !== $expected) {
        $issues[] = sprintf('%s loaded=%s, expected=%s.', $provider, $actual ? 'true' : 'false', $expected ? 'true' : 'false');
    }
}

if ($isLoaded(AIAgentServiceProvider::class) && ! in_array('ai-agent', $enabledExtensions, true)) {
    $issues[] = 'AIAgentServiceProvider loaded without explicit enablement.';
}

$result = [
    'flags' => [
        'workcore' => $flags->workCoreEnabled(),
        'chatbot' => $flags->chatbotEnabled(),
        'interaction_engine' => $flags->interactionEngineEnabled(),
        'extension_discovery' => $flags->extensionDiscoveryEnabled(),
    ],
    'enabled_extensions' => $enabledExtensions,
    'providers' => [
        'workcore' => $isLoaded(WorkCoreServiceProvider::class),
        'chatbot' => $isLoaded(ChatbotServiceProvider::class),
        'marketplace' => $isLoaded(MarketplaceServiceProvider::class),
        'focus_mode' => $isLoaded(FocusModeServiceProvider::class),
        'ai_agent' => $isLoaded(AIAgentServiceProvider::class),
    ],
    'issues' => $issues,
];

fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
exit($issues === [] ? 0 : 1);
