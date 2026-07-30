<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Contracts\ProviderHttpTransport;
use App\Extensions\TitanMapsIntelligence\Contracts\SecretResolver;
use App\Extensions\TitanMapsIntelligence\DTO\Coordinates;
use App\Extensions\TitanMapsIntelligence\DTO\NearbySearchRequest;
use App\Extensions\TitanMapsIntelligence\DTO\PlaceSearchRequest;
use App\Extensions\TitanMapsIntelligence\Providers\GooglePlacesProvider;

return static function (): void {
    $transport = new class implements ProviderHttpTransport {
        public array $calls = [];
        public function request(string $method, string $url, array $headers = [], array $json = [], int $timeoutSeconds = 10, int $retryAttempts = 2): array
        {
            $this->calls[] = compact('method', 'url', 'headers', 'json', 'timeoutSeconds', 'retryAttempts');
            return [
                'places' => [[
                    'id' => 'place-1',
                    'displayName' => ['text' => 'Northside Plumbing'],
                    'formattedAddress' => '1 High St, Melbourne VIC',
                    'location' => ['latitude' => -37.81, 'longitude' => 144.96],
                    'types' => ['plumber'],
                    'primaryType' => 'plumber',
                    'rating' => 4.7,
                    'userRatingCount' => 48,
                    'nationalPhoneNumber' => '03 9000 0000',
                    'websiteUri' => 'https://northside.example',
                    'googleMapsUri' => 'https://maps.google.com/?cid=1',
                    'businessStatus' => 'OPERATIONAL',
                ]],
            ];
        }
    };
    $secrets = new class implements SecretResolver {
        public function resolve(string $credentialReference): string
        {
            assert_same('vault://company/google-places', $credentialReference);
            return 'api-key-value';
        }
    };

    $provider = new GooglePlacesProvider($transport, $secrets, [
        'credential_reference' => 'vault://company/google-places',
        'base_uri' => 'https://places.googleapis.com',
        'timeout_seconds' => 7,
        'retry_attempts' => 1,
        'field_masks' => [
            'search' => 'places.id,places.displayName,places.formattedAddress,places.location,places.types,places.primaryType,places.rating,places.userRatingCount,places.nationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.businessStatus',
            'details' => 'id,displayName',
        ],
    ]);

    $page = $provider->search(new PlaceSearchRequest('plumber Melbourne', 10, 'en', 'AU'));
    assert_same('Northside Plumbing', $page->places[0]->name);
    $call = $transport->calls[0];
    assert_same('POST', $call['method']);
    assert_same('https://places.googleapis.com/v1/places:searchText', $call['url']);
    assert_same('api-key-value', $call['headers']['X-Goog-Api-Key']);
    assert_true(str_contains($call['headers']['X-Goog-FieldMask'], 'places.id'));
    assert_true(! str_contains($call['headers']['X-Goog-FieldMask'], '*'));
    assert_same('plumber Melbourne', $call['json']['textQuery']);
    assert_same(7, $call['timeoutSeconds']);
    assert_same(1, $call['retryAttempts']);

    $provider->nearby(new NearbySearchRequest(new Coordinates(-37.81, 144.96), 15000, ['plumber'], 10, 'en', 'AU'));
    $nearby = $transport->calls[1];
    assert_same('https://places.googleapis.com/v1/places:searchNearby', $nearby['url']);
    assert_same(15000.0, $nearby['json']['locationRestriction']['circle']['radius']);
    assert_same(['plumber'], $nearby['json']['includedTypes']);
};
