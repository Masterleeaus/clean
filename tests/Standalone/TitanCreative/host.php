<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$summary = (string) file_get_contents($root.'/app/Http/Controllers/Titan/WorkCoreSummaryController.php');
$expect(str_contains($summary, "'creative_marketing' => \$creativeMarketing"), 'Titan summary exposes creative_marketing section.');
foreach (['active_campaigns','open_projects','generation_queue','pending_approvals','pending_publications','active_automations'] as $key) {
    $expect(str_contains($summary, "'{$key}'"), "Titan summary exposes {$key}.");
}

$context = (string) file_get_contents($root.'/app/Titan/AI/ConversationContextBuilder.php');
$expect(str_contains($context, "'creative_marketing' => \$this->creativeMarketingCounts"), 'Titan Zero context includes bounded creative marketing counts.');
$expect(str_contains($context, 'private function creativeMarketingCounts'), 'Context builder has creative marketing count method.');
foreach (['campaigns','projects','generation_jobs','pending_approvals','publications'] as $key) {
    $expect(str_contains($context, "'{$key}'"), "Context exposes {$key} count.");
}
$expect(! str_contains($context, 'storage_reference'), 'Context does not expose private asset references.');
$expect(! str_contains($context, 'provider_metadata'), 'Context does not expose raw provider metadata.');

$view = (string) file_get_contents($root.'/resources/views/titan/operations.blade.php');
$expect(str_contains($view, 'data-titan-creative-summary'), 'Operations includes creative marketing summary panel.');
foreach (['active_campaigns','open_projects','generation_queue','pending_approvals','pending_publications','active_automations'] as $key) {
    $expect(str_contains($view, "data-titan-creative-count=\"{{ \$key }}\""), "Operations renders {$key}.");
}

foreach (['resources/js/titan/operations.js','public/js/titan-operations.js'] as $asset) {
    $js = (string) file_get_contents($root.'/'.$asset);
    $expect(str_contains($js, 'creative_marketing'), "{$asset} consumes creative_marketing summary.");
    $expect(str_contains($js, 'data-titan-creative-count'), "{$asset} renders creative marketing counts.");
    foreach (['assurance', 'property_accommodation', 'intelligence_runtime', 'finance', 'payments', 'trust_accounting'] as $section) {
        $expect(str_contains($js, $section), "{$asset} preserves the {$section} operations panel during frontend builds.");
    }
}

fwrite(STDOUT, "Titan Creative host: {$checks} checks passed.\n");
