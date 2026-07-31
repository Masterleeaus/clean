<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$configPath = $root.'/config/titan_creative.php';
$expect(is_file($configPath), 'Titan Creative configuration must exist.');
$config = is_file($configPath) ? require $configPath : [];
$donors = $config['donor_classification'] ?? [];
$expect(is_array($donors) && count($donors) === 30, 'All 30 valid donor packages require an explicit decision.');
$allowed = ['integrated_concept','optional_adapter','deferred_provider','rejected_duplicate_authority','quarantined'];
foreach ($donors as $package => $decision) {
    $expect(is_array($decision), "{$package} decision is structured.");
    $expect(in_array($decision['decision'] ?? null, $allowed, true), "{$package} uses an allowed decision.");
    $expect(isset($decision['accepted_concepts']) && is_array($decision['accepted_concepts']), "{$package} lists accepted concepts.");
    $expect(isset($decision['rejected_authorities']) && is_array($decision['rejected_authorities']), "{$package} lists rejected authorities.");
}
$providers = $config['provider_adapters'] ?? [];
$expect(is_array($providers) && count($providers) >= 12, 'Provider adapter catalogue is explicit.');
foreach ($providers as $provider => $definition) {
    $expect(($definition['status'] ?? null) !== 'enabled', "{$provider} is not silently enabled.");
    $expect(! array_key_exists('api_key', $definition), "{$provider} stores no API key.");
    $expect(! array_key_exists('access_token', $definition), "{$provider} stores no access token.");
}

$doc = $root.'/docs/integration/MARKETING_CREATIVE_RECONCILIATION.md';
$expect(is_file($doc), 'Marketing and Creative reconciliation report exists.');
$docSource = is_file($doc) ? (string) file_get_contents($doc) : '';
foreach (['Authority boundaries','Integrated concepts','Optional adapters','Rejected duplicate authorities','Quarantined','Provider activation boundary'] as $heading) {
    $expect(str_contains($docSource, $heading), "Reconciliation report contains {$heading}.");
}
$expect(! preg_match('/\b(?:TBD|TODO|FIXME)\b/i', $docSource), 'Reconciliation report has no unresolved placeholders.');

$provider = (string) file_get_contents($root.'/app/Titan/Creative/Providers/TitanCreativeServiceProvider.php');
$repository = (string) file_get_contents($root.'/app/Titan/Creative/Repositories/DatabaseCreativeRepository.php');
foreach (['Http::','GuzzleHttp','curl_exec','file_get_contents($url','api_key','access_token'] as $needle) {
    $expect(! str_contains($provider.$repository, $needle), "Runtime excludes direct provider or secret pattern: {$needle}.");
}
foreach (['Marketing & Creative Extensions','blogpilot.zip','creative-suite.zip','fashion-studio.zip'] as $needle) {
    $expect(! str_contains($provider.$repository, $needle), "Runtime does not autoload donor package: {$needle}.");
}

$verifier = (string) file_get_contents($root.'/tools/titan_verify.php');
$expect(str_contains($verifier, "'creative' => ["), 'Release verifier has creative section.');
$expect(str_contains($verifier, 'titan_creative.php'), 'Release verifier requires creative configuration.');
$expect(str_contains($verifier, 'MARKETING_CREATIVE_RECONCILIATION.md'), 'Release verifier requires reconciliation report.');
$expect(str_contains($verifier, 'donor package decision'), 'Release verifier checks donor decisions.');

fwrite(STDOUT, "Titan Creative reconciliation: {$checks} checks passed.\n");
