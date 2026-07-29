<?php

declare(strict_types=1);

return static function (): void {
    $files = glob(dirname(__DIR__, 4).'/database/migrations/extensions/titan_maps/*.php') ?: [];
    assert_true(count($files) >= 12, 'At least twelve extension migrations are required');
    $content = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
    foreach ([
        'maps_provider_connections', 'discovery_searches', 'discovery_runs', 'external_places',
        'external_place_contacts', 'field_observations', 'discovery_candidates', 'candidate_matches',
        'candidate_promotions', 'territory_analyses', 'maps_usage_records', 'maps_suppressions',
    ] as $table) {
        assert_true(str_contains($content, "Schema::create('{$table}'"), "Missing table {$table}");
    }
    assert_true(substr_count($content, "string('company_id'") >= 12, 'Every company-owned table must declare company_id');
};
