<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\DTO\Coordinates;
use App\Extensions\TitanMapsIntelligence\DTO\ExternalPlaceData;
use App\Extensions\TitanMapsIntelligence\Services\PlaceCanonicalizer;

return static function (): void {
    $canonicalizer = new PlaceCanonicalizer();
    $place = new ExternalPlaceData(
        provider: 'google-places',
        providerPlaceId: 'abc123',
        name: ' Northside   Plumbing Pty Ltd ',
        address: '1 High Street, Melbourne VIC 3000',
        coordinates: new Coordinates(-37.81, 144.96),
        phone: '+61 (03) 9000 0000',
        website: 'https://WWW.Northside.Example/path/',
        email: 'Info@Northside.Example',
        categories: ['plumber'],
    );
    assert_same('google-places:abc123', $canonicalizer->canonicalKey($place));
    assert_same('0390000000', $canonicalizer->normalizePhone($place->phone));
    assert_same('northside.example', $canonicalizer->normalizeDomain($place->website));
    assert_same('info@northside.example', $canonicalizer->normalizeEmail($place->email));
};
