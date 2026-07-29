<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\GeoDistanceService;

return static function (): void {
    $service = new GeoDistanceService();
    assert_true(abs($service->kilometres(-37.8136, 144.9631, -37.8136, 144.9631)) < 0.000001);
    $distance = $service->kilometres(-37.8136, 144.9631, -37.8183, 144.9671);
    assert_true($distance > 0.5 && $distance < 0.8, 'Expected a bounded Melbourne CBD distance');
};
