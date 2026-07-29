<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$migration = $root.'/database/migrations/2026_07_26_060000_create_titan_creative_marketing_tables.php';
$expect(is_file($migration), 'Creative marketing migration must exist.');
$source = is_file($migration) ? (string) file_get_contents($migration) : '';
$tables = [
    'titan_brand_kits','titan_brand_assets','titan_audiences','titan_content_templates','titan_creative_projects',
    'titan_creative_assets','titan_creative_revisions','titan_generation_jobs','titan_generation_outputs','titan_campaigns',
    'titan_campaign_objectives','titan_content_items','titan_content_calendar_entries','titan_publication_channels','titan_publications',
    'titan_marketing_automations','titan_marketing_automation_runs','titan_seo_briefs','titan_newsletters','titan_analytics_observations','titan_content_approvals',
];
foreach ($tables as $table) {
    $expect(str_contains($source, "Schema::create('{$table}'"), "Migration creates {$table}.");
}
$expect(substr_count($source, "foreignId('company_id')") >= count($tables), 'Every creative/marketing table is company scoped.');
$expect(str_contains($source, "string('storage_reference')"), 'Creative assets use private storage references.');
$expect(! str_contains($source, "string('api_key')"), 'Migration stores no plaintext API key.');
$expect(! str_contains($source, "string('access_token')"), 'Migration stores no plaintext access token.');
$expect(str_contains($source, "string('provider_connection_public_id'"), 'Generation jobs use provider connection references.');
$expect(str_contains($source, "string('connection_public_id'"), 'Publication channels use connector references.');
$expect(str_contains($source, "string('entity_public_id'"), 'Marketing can reference WorkCore public ids without copying ownership.');
$expect(str_contains($source, "string('source_reference'"), 'Analytics observations retain source attribution.');

$contract = $root.'/app/Titan/Creative/Contracts/CreativeRepository.php';
$repository = $root.'/app/Titan/Creative/Repositories/DatabaseCreativeRepository.php';
$expect(is_file($contract), 'Creative repository contract must exist.');
$expect(is_file($repository), 'Database creative repository must exist.');
$contractSource = is_file($contract) ? (string) file_get_contents($contract) : '';
$repositorySource = is_file($repository) ? (string) file_get_contents($repository) : '';
foreach ([
    'createBrandKit','createAudience','createTemplate','createProject','registerAsset','queueGeneration','transitionGeneration',
    'createCampaign','createContent','scheduleContent','createPublicationChannel','requestPublication','transitionPublication',
    'createAutomation','createSeoBrief','createNewsletter','decideApproval','recordAnalytics','summary',
] as $method) {
    $expect(str_contains($contractSource, "function {$method}"), "Repository contract exposes {$method}.");
    $expect(str_contains($repositorySource, "function {$method}"), "Database repository implements {$method}.");
}
foreach (['CampaignSchedulePolicy','ContentApprovalPolicy','GenerationJobState','PublicationState','ProviderReferencePolicy'] as $policy) {
    $expect(str_contains($repositorySource, $policy), "Repository enforces {$policy}.");
}
$expect(str_contains($repositorySource, 'assertCompany($companyId)'), 'Repository validates active company id.');
$expect(! str_contains($repositorySource, "['company_id']"), 'Repository does not trust payload company authority.');
$expect(str_contains($repositorySource, "where('company_id', \$companyId)"), 'Repository reads remain company scoped.');
$expect(str_contains($repositorySource, "titan_provider_connections"), 'Repository validates provider connections through Titan Intelligence.');
$expect(str_contains($repositorySource, "titan_connections"), 'Repository validates connector connections through Titan Intelligence.');

fwrite(STDOUT, "Titan Creative persistence: {$checks} checks passed.\n");
