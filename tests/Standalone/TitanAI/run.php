<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/Titan/AI/TitanZeroOrchestrator.php';
require_once $root . '/app/Titan/AI/ToolRouter.php';
require_once $root . '/app/Services/AIAssistantService.php';

$failures = [];
$passes = [];
$assert = static function (bool $condition, string $label) use (&$failures, &$passes): void {
    if ($condition) {
        $passes[] = $label;
    } else {
        $failures[] = $label;
    }
};

$orchestrator = (new ReflectionClass(App\Titan\AI\TitanZeroOrchestrator::class))->newInstanceWithoutConstructor();
$envelope = new ReflectionMethod($orchestrator, 'toolEnvelope');
$envelope->setAccessible(true);
$parsed = $envelope->invoke($orchestrator, '{"arguments":{"query":"plumber"},"confirmation_id":null,"tool":"titan-maps-intelligence.search.businesses"}');
$assert(is_array($parsed) && ($parsed['tool'] ?? null) === 'titan-maps-intelligence.search.businesses', 'tool envelope accepts valid keys in any JSON order');
$assert($envelope->invoke($orchestrator, '{"tool":"x","arguments":{},"confirmation_id":null,"extra":true}') === null, 'tool envelope rejects extra fields');
$assert($envelope->invoke($orchestrator, '{"tool":"x","arguments":{},"confirmation_id":"invented-by-model"}') === null, 'AI output cannot manufacture explicit confirmation');
$assert($envelope->invoke($orchestrator, 'ordinary text') === null, 'ordinary assistant text is not treated as a tool call');

$router = (new ReflectionClass(App\Titan\AI\ToolRouter::class))->newInstanceWithoutConstructor();
$tenantOverride = new ReflectionMethod($router, 'containsTenantOverride');
$tenantOverride->setAccessible(true);
$assert($tenantOverride->invoke($router, ['nested' => ['company_id' => 7]]) === true, 'tool router rejects nested snake-case tenant authority');
$assert($tenantOverride->invoke($router, ['companyId' => 7]) === true, 'tool router rejects camel-case tenant authority');
$assert($tenantOverride->invoke($router, ['conversation_id' => 7, 'query' => 'cleaner']) === false, 'tool router preserves legitimate identifiers');

$assistant = (new ReflectionClass(App\Services\AIAssistantService::class))->newInstanceWithoutConstructor();
$normalise = new ReflectionMethod($assistant, 'normaliseMessages');
$normalise->setAccessible(true);
$messages = $normalise->invoke($assistant, [
    ['role' => 'system', 'content' => ' policy '],
    ['role' => 'invalid', 'content' => 'discard'],
    ['role' => 'user', 'content' => ' hello '],
]);
$assert(count($messages) === 2 && $messages[1]['content'] === 'hello', 'AI messages are normalised and invalid roles are discarded');

printf("Titan AI standalone tests: %d passed, %d failed\n", count($passes), count($failures));
foreach ($failures as $failure) {
    fwrite(STDERR, "FAIL: {$failure}\n");
}

exit($failures === [] ? 0 : 1);
