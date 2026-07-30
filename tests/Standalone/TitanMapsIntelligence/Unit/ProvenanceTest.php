<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\DTO\Coordinates;
use App\Extensions\TitanMapsIntelligence\DTO\ExternalPlaceData;
use App\Extensions\TitanMapsIntelligence\Services\ProvenanceService;

return static function (): void {
    $place = new ExternalPlaceData(
        provider: 'google-places',
        providerPlaceId: 'p1',
        name: 'Northside Plumbing',
        address: '1 High St',
        coordinates: new Coordinates(-37.8, 144.9),
        phone: '0390000000',
        categories: ['plumber'],
        rating: 4.5,
    );
    $observations = (new ProvenanceService())->observations($place, new DateTimeImmutable('2026-07-24T12:00:00+10:00'));
    $fields = array_column($observations, 'field');
    assert_true(in_array('name', $fields, true));
    assert_true(in_array('latitude', $fields, true));
    assert_true(in_array('rating', $fields, true));
    foreach ($observations as $observation) {
        assert_same('google-places', $observation['provider']);
        assert_same('2026-07-24T12:00:00+10:00', $observation['observed_at']);
        assert_true($observation['confidence'] >= 0 && $observation['confidence'] <= 1);
    }
};
