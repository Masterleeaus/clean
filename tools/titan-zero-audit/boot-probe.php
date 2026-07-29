<?php

declare(strict_types=1);

use App\Domains\Marketplace\MarketplaceServiceProvider;
use App\Domains\WorkCore\WorkCoreServiceProvider;
use App\Extensions\AIAgent\System\AIAgentServiceProvider;
use App\Extensions\Chatbot\System\ChatbotServiceProvider;
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

$isLoaded = static fn (string $provider): bool => ($loaded[$provider] ?? false) === true;
$issues = [];

$expectations = [
    WorkCoreServiceProvider::class => $flags->workCoreEnabled(),
    ChatbotServiceProvider::class => $flags->chatbotEnabled(),
    MarketplaceServiceProvider::class => true,
];

foreach ($expectations as $provider => $expected) {
    $actual = $isLoaded($provider);
    if ($actual !== $expected) {
        $issues[] = sprintf('%s loaded=%s, expected=%s.', $provider, $actual ? 'true' : 'false', $expected ? 'true' : 'false');
    }
}

if (! $flags->extensionDiscoveryEnabled() && $isLoaded(AIAgentServiceProvider::class)) {
    $issues[] = 'AIAgentServiceProvider loaded while extension discovery was disabled.';
}

$result = [
    'flags' => [
        'workcore' => $flags->workCoreEnabled(),
        'chatbot' => $flags->chatbotEnabled(),
        'interaction_engine' => $flags->interactionEngineEnabled(),
        'extension_discovery' => $flags->extensionDiscoveryEnabled(),
    ],
    'providers' => [
        'workcore' => $isLoaded(WorkCoreServiceProvider::class),
        'chatbot' => $isLoaded(ChatbotServiceProvider::class),
        'marketplace' => $isLoaded(MarketplaceServiceProvider::class),
        'ai_agent' => $isLoaded(AIAgentServiceProvider::class),
    ],
    'issues' => $issues,
];

fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
exit($issues === [] ? 0 : 1);
