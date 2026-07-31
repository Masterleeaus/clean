<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\ProviderRankingService;

return static function (): void {
    $service = new ProviderRankingService();
    $ranked = $service->rank([
        'distance_km' => 2.5,
        'category_match' => 1.0,
        'rating' => 4.8,
        'review_count' => 120,
        'open_now' => true,
        'phone' => '0390000000',
        'website' => 'https://example.com',
        'data_freshness' => 0.9,
    ]);

    assert_true($ranked->score > 0.75, 'Strong nearby provider should rank highly');
    assert_same(['distance', 'category', 'rating', 'review_count', 'open_now', 'contactability', 'freshness'], array_keys($ranked->breakdown));
    assert_true(abs(array_sum($ranked->breakdown) - $ranked->score) < 0.0001);
};
