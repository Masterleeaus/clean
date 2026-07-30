<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };

$providerPath = $root.'/app/Titan/Intelligence/Providers/TitanIntelligenceServiceProvider.php';
$configPath = $root.'/config/titan_intelligence.php';
$bootstrapPath = $root.'/bootstrap/providers.php';
foreach ([$providerPath,$configPath,$bootstrapPath] as $path) $expect(is_file($path), basename($path).' is missing.');
$provider = is_file($providerPath) ? file_get_contents($providerPath) : '';
$config = is_file($configPath) ? file_get_contents($configPath) : '';
$bootstrap = file_get_contents($bootstrapPath);

$capabilities = [
 'titan.workspace.folder.create','titan.workspace.canvas.create','titan.workspace.share.create','titan.memory.store',
 'titan.skills.create','titan.skills.assign','titan.agents.create','titan.agents.tool.assign','titan.connectors.create','titan.providers.connection.create',
 'titan.models.upsert','titan.routing.policy.create','titan.model_council.start','titan.voice.profile.create',
 'titan.voice.session.start','titan.announcements.publish','titan.onboarding.flow.create','titan.onboarding.progress.record','titan.intelligence.summary',
];
foreach ($capabilities as $id) $expect(str_contains($provider, "'{$id}'"), "Capability {$id} is not registered.");
$permissions = ['titan.workspace.write','titan.memory.write','titan.skills.manage','titan.agents.manage','titan.connectors.manage','titan.providers.manage','titan.voice.manage','titan.announcements.manage','titan.onboarding.write','titan.intelligence.read'];
foreach ($permissions as $permission) $expect(str_contains($provider, "'{$permission}'"), "Permission {$permission} is not declared.");
$expect(str_contains($bootstrap, 'TitanIntelligenceServiceProvider::class'), 'Titan intelligence provider is not registered in Laravel 12 bootstrap/providers.php.');
$expect(str_contains($provider, 'IntelligenceRepository::class'), 'Repository contract binding is missing.');
$expect(str_contains($provider, 'DatabaseIntelligenceRepository::class'), 'Database repository binding is missing.');
$expect(! str_contains($provider, 'Http::'), 'Service provider must not call external providers.');
$expect(! str_contains($provider, 'tz_work_orders'), 'Titan intelligence provider must not write WorkCore tables.');

$actionsDir = $root.'/app/Titan/Intelligence/Actions';
$handlers = glob($actionsDir.'/*.php') ?: [];
$expect(count($handlers) >= 19, 'Focused capability handlers are missing.');
$allHandlers = '';
foreach ($handlers as $handler) {
    $text = file_get_contents($handler); $allHandlers .= $text;
    $expect(str_contains($text, 'ActiveCompanyContext'), basename($handler).' must resolve server company context.');
    $expect(! preg_match('/[\'\"]company_id[\'\"]\s*=>\s*\$input/', $text), basename($handler).' trusts client company authority.');
    $expect(! str_contains($text, 'Http::'), basename($handler).' must not call external providers.');
}
$expect(str_contains($allHandlers, 'BusinessActionRegistry'), 'Agent tool assignment must validate WorkCore actions.');
$expect(str_contains($allHandlers, 'ReadModelRegistry'), 'Agent tool assignment must validate WorkCore reads.');
$expect(str_contains($allHandlers, 'CapabilityRegistry'), 'Agent tool assignment must validate Titan capabilities.');
$expect(str_contains($allHandlers, "config('titan_intelligence.connectors'"), 'Connector handler must use configured connector allowlist.');
$expect(str_contains($allHandlers, "config('titan_intelligence.providers'"), 'Provider/model handler must use configured provider allowlist.');

foreach (['gmail','outlook','google-drive','google-calendar','notion','slack','whatsapp','telegram','messenger','instagram'] as $connector) {
    $expect(str_contains($config, "'{$connector}'"), "Connector definition {$connector} is missing.");
}
foreach (['openai','azure-openai','openrouter','perplexity','gemini','anthropic','ollama'] as $providerKey) {
    $expect(str_contains($config, "'{$providerKey}'"), "Provider definition {$providerKey} is missing.");
}
foreach (['twilio','elevenlabs','azure-tts','openai-realtime'] as $voiceProvider) {
    $expect(str_contains($config, "'{$voiceProvider}'"), "Voice provider definition {$voiceProvider} is missing.");
}
foreach (['ugc-creator','ugc-factory','ai-captions','ai-avatar','ai-persona','influencer-avatar'] as $deferred) {
    $expect(str_contains($config, "'{$deferred}' => 'deferred-marketing-creative'"), "Deferred donor {$deferred} is not classified.");
}

fwrite(STDOUT, "Titan Intelligence runtime: {$checks} checks passed.\n");
