<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\DTO\Coordinates;
use App\Extensions\TitanMapsIntelligence\DTO\NearbySearchRequest;
use App\Extensions\TitanMapsIntelligence\DTO\PlaceSearchRequest;
use App\Extensions\TitanMapsIntelligence\Exceptions\MapsIntelligenceException;

return static function (): void {
    $coordinates = new Coordinates(-37.8136, 144.9631);
    assert_same(-37.8136, $coordinates->latitude);
    assert_same(144.9631, $coordinates->longitude);

    assert_throws(fn () => new Coordinates(-91, 10), MapsIntelligenceException::class, 'MAPS_INVALID_COORDINATES');
    assert_throws(fn () => new Coordinates(10, 181), MapsIntelligenceException::class, 'MAPS_INVALID_COORDINATES');
    assert_throws(fn () => new PlaceSearchRequest('  ', 10), MapsIntelligenceException::class, 'MAPS_INVALID_SEARCH');
    assert_throws(fn () => new PlaceSearchRequest('plumber', 0), MapsIntelligenceException::class, 'MAPS_SEARCH_LIMIT_EXCEEDED');
    assert_throws(fn () => new PlaceSearchRequest('plumber', 101), MapsIntelligenceException::class, 'MAPS_SEARCH_LIMIT_EXCEEDED');
    assert_throws(fn () => new NearbySearchRequest($coordinates, 50001, ['plumber'], 10), MapsIntelligenceException::class, 'MAPS_INVALID_RADIUS');

    $request = new PlaceSearchRequest('emergency plumber Melbourne', 20, 'en', 'AU');
    assert_same('emergency plumber Melbourne', $request->query);
    assert_same(20, $request->maximumResults);
};
