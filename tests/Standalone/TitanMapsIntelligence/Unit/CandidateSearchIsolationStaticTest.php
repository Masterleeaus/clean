<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    $migration = (string) file_get_contents($root.'/database/migrations/extensions/titan_maps/2026_07_25_020007_create_discovery_candidates_table.php');
    assert_true(str_contains($migration, "['company_id', 'discovery_search_id', 'external_place_id', 'candidate_type']"), 'Candidate identity must be scoped to its discovery search.');

    $job = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Jobs/ProcessDiscoveryPage.php');
    $identityStart = strpos($job, 'firstOrCreate([');
    $identityEnd = strpos($job, '], [', $identityStart === false ? 0 : $identityStart);
    assert_true($identityStart !== false && $identityEnd !== false, 'Candidate firstOrCreate identity not found.');
    $identity = substr($job, $identityStart, $identityEnd - $identityStart);
    assert_true(str_contains($identity, "'discovery_search_id' => \$search->getKey()"), 'Candidate creation identity must include the active search.');
    assert_true(str_contains($job, 'wasRecentlyCreated'), 'Candidate-created events must be idempotent across queue retries.');
};
