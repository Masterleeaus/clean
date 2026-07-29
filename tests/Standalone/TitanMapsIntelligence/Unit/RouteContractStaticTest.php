<?php

declare(strict_types=1);

return static function (): void {
    $route = (string) file_get_contents(dirname(__DIR__, 4).'/app/Extensions/TitanMapsIntelligence/routes/api.php');
    assert_true(str_contains($route, "'auth:sanctum'"));
    assert_true(str_contains($route, "'titan.company'"));
    assert_true(str_contains($route, 'SearchController'));
    assert_true(str_contains($route, 'CandidateController'));
    assert_true(str_contains($route, 'TerritoryController'));
    assert_true(str_contains($route, 'UsageController'));
    assert_true(str_contains($route, 'ExportController'));
    assert_true(! str_contains($route, '{company_id}'), 'Routes must not accept company ID as authority');
};
