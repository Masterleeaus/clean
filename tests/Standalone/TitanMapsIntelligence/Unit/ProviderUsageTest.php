<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\DTO\ProviderUsage;
use App\Extensions\TitanMapsIntelligence\Services\ProviderUsageService;

return static function (): void {
    $summary = (new ProviderUsageService())->summarise([
        new ProviderUsage('google-places', 'text_search', 1, 10, 1.0, 0.032, 'AUD'),
        new ProviderUsage('google-places', 'text_search', 1, 6, 1.0, 0.032, 'AUD'),
        new ProviderUsage('google-places', 'details', 2, 2, 2.0, 0.020, 'AUD'),
    ]);
    assert_same(4, $summary['request_count']);
    assert_same(18, $summary['result_count']);
    assert_true(abs($summary['estimated_cost'] - 0.084) < 0.0001);
    assert_same('AUD', $summary['currency']);
};
