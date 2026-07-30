<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };

$contextPath = $root.'/app/Titan/AI/ConversationContextBuilder.php';
$summaryPath = $root.'/app/Http/Controllers/Titan/WorkCoreSummaryController.php';
$viewPath = $root.'/resources/views/titan/operations.blade.php';
$jsPath = $root.'/public/js/titan-operations.js';
foreach ([$contextPath,$summaryPath,$viewPath,$jsPath] as $path) $expect(is_file($path), basename($path).' is missing.');
$context = file_get_contents($contextPath); $summary = file_get_contents($summaryPath); $view = file_get_contents($viewPath); $js = file_get_contents($jsPath);

$expect(str_contains($context, "'intelligence' =>"), 'Conversation context does not include intelligence summary.');
foreach (['titan_workspace_canvases','titan_memories','titan_skills','titan_agents','titan_connections','titan_voice_sessions'] as $table) {
    $expect(str_contains($context, "'{$table}'") || str_contains($context, "=> '{$table}'"), "Conversation context summary does not count {$table}.");
}
foreach (['content','token_hash','credential_reference','system_instruction','response_text'] as $sensitive) {
    $expect(! str_contains($context, "select('{$sensitive}'"), "Conversation context exposes {$sensitive}.");
}
$expect(str_contains($summary, "'intelligence_runtime'"), 'API summary does not expose intelligence_runtime section.');
foreach (['active_agents','active_connections','active_memories','published_skills','active_voice_sessions','pending_onboarding'] as $key) {
    $expect(str_contains($summary, "'{$key}'"), "API intelligence metric {$key} is missing.");
    $expect(str_contains($view, "'{$key}'"), "Operations metric {$key} is missing from the view definition.");
}
$expect(str_contains($view, 'data-titan-intelligence-summary'), 'Operations intelligence panel is missing.');
$expect(str_contains($view, 'Titan Intelligence Runtime'), 'Operations panel title is missing.');
$expect(str_contains($js, 'data-titan-intelligence-count'), 'Operations JavaScript does not render intelligence metrics.');
$expect(str_contains($js, 'intelligence_runtime'), 'Operations JavaScript does not read intelligence runtime summary.');
$expect(! str_contains($view, 'credential_reference'), 'Operations view must not expose credential references.');
$expect(! str_contains($view, 'token_hash'), 'Operations view must not expose share token hashes.');

fwrite(STDOUT, "Titan Intelligence host surface: {$checks} checks passed.\n");
