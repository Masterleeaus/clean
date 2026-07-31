<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\TerritoryAnalysisService;

return static function (): void {
    $service = new TerritoryAnalysisService();
    $analysis = $service->analyse([
        ['primary_category' => 'plumber', 'distance_km' => 2.2, 'provider' => 'google-places', 'confidence' => 0.9],
        ['primary_category' => 'plumber', 'distance_km' => 8.4, 'provider' => 'google-places', 'confidence' => 0.8],
        ['primary_category' => 'electrician', 'distance_km' => 21.0, 'provider' => 'other', 'confidence' => 0.7],
    ]);
    assert_same(3, $analysis['total_places']);
    assert_same(2, $analysis['categories']['plumber']);
    assert_same(1, $analysis['distance_bands']['0-5km']);
    assert_same(1, $analysis['distance_bands']['5-15km']);
    assert_same(1, $analysis['distance_bands']['15km+']);
    assert_same(2, count($analysis['source_coverage']));
    assert_true(abs($analysis['average_confidence'] - 0.8) < 0.0001);
};
