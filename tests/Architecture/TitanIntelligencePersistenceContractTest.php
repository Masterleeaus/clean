<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };

$migrationPath = $root.'/database/migrations/2026_07_26_050000_create_titan_intelligence_runtime_tables.php';
$contractPath = $root.'/app/Titan/Intelligence/Contracts/IntelligenceRepository.php';
$repositoryPath = $root.'/app/Titan/Intelligence/Repositories/DatabaseIntelligenceRepository.php';
foreach ([$migrationPath, $contractPath, $repositoryPath] as $path) $expect(is_file($path), basename($path).' is missing.');
$migration = is_file($migrationPath) ? file_get_contents($migrationPath) : '';
$contract = is_file($contractPath) ? file_get_contents($contractPath) : '';
$repository = is_file($repositoryPath) ? file_get_contents($repositoryPath) : '';

$tables = [
 'titan_workspace_folders','titan_workspace_canvases','titan_workspace_shares','titan_memories',
 'titan_skills','titan_skill_versions','titan_skill_assignments','titan_agents','titan_agent_tools',
 'titan_agent_knowledge_sources','titan_agent_channels','titan_agent_workflows','titan_agent_runs',
 'titan_connections','titan_connection_sync_states','titan_provider_connections','titan_model_catalogue',
 'titan_routing_policies','titan_model_council_runs','titan_model_council_responses',
 'titan_voice_profiles','titan_voice_sessions','titan_voice_transcripts','titan_voice_events',
 'titan_announcements','titan_announcement_receipts','titan_onboarding_flows','titan_onboarding_steps','titan_onboarding_progress',
];
foreach ($tables as $table) {
    $expect(str_contains($migration, "Schema::create('{$table}'"), "Missing table {$table}.");
}
$expect(substr_count($migration, "foreignId('company_id')") >= count($tables), 'Every runtime table must declare company_id.');
foreach (['credential_reference','token_reference','secret_reference'] as $field) {
    $expect(str_contains($migration, $field), "Vault reference field {$field} is missing.");
}
foreach (['api_key','access_token','refresh_token','client_secret','password'] as $forbidden) {
    $expect(! preg_match("/->(?:string|text)\\('{$forbidden}'/", $migration), "Forbidden plaintext secret column {$forbidden} exists.");
}
$expect(str_contains($migration, "string('token_hash', 64)"), 'Share token must be stored as SHA-256 hash.');
$expect(str_contains($migration, "unique(['company_id', 'public_id'])"), 'Public ids must be company scoped.');
$expect(str_contains($migration, "index(['company_id', 'status'])"), 'Lifecycle records need company/status indexes.');

preg_match_all("/Schema::create\('([^']+)'[^}]+?\n        }\);/s", $migration, $blocks, PREG_SET_ORDER);
foreach ($blocks as $block) {
    if (str_contains($block[0], "index(['company_id', 'status'])")) {
        $expect(str_contains($block[0], "string('status'"), "{$block[1]} indexes a missing status column.");
    }
}

$methods = ['createFolder','createCanvas','createShare','storeMemory','createSkill','assignSkill','createAgent','assignAgentTool','createConnection','createProviderConnection','upsertModel','createRoutingPolicy','startCouncilRun','createVoiceProfile','startVoiceSession','publishAnnouncement','createOnboardingFlow','recordOnboardingProgress','summary'];
foreach ($methods as $method) {
    $expect(str_contains($contract, "function {$method}"), "Repository contract method {$method} is missing.");
    $expect(str_contains($repository, "function {$method}"), "Repository implementation method {$method} is missing.");
}
$expect(str_contains($repository, "where('company_id', \$companyId)"), 'Repository queries must scope company_id.');
$expect(str_contains($repository, "getSchemaBuilder()->hasTable"), 'Intelligence summary must fail closed before migrations are installed.');
$expect(! str_contains($repository, 'Http::'), 'Repository must not call external providers.');
$expect(! str_contains($repository, 'tz_work_orders'), 'Titan intelligence repository must not write WorkCore tables.');

fwrite(STDOUT, "Titan Intelligence persistence: {$checks} checks passed.\n");
