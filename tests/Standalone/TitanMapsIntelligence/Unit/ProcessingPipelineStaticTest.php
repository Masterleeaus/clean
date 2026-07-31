<?php

declare(strict_types=1);

return static function (): void {
    $job = (string) file_get_contents(dirname(__DIR__, 4).'/app/Extensions/TitanMapsIntelligence/Jobs/ProcessDiscoveryPage.php');
    assert_true(str_contains($job, 'SuppressionService'));
    assert_true(str_contains($job, 'MapsSuppression'));
    assert_true(str_contains($job, 'ProviderRankingService'));
    assert_true(str_contains($job, 'GeoDistanceService'));
    assert_true(str_contains($job, "'relevance_score'"));
    assert_true(str_contains($job, "'results_skipped'"));
};
