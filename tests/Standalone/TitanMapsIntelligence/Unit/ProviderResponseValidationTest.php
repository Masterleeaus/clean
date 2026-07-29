<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Exceptions\ProviderException;
use App\Extensions\TitanMapsIntelligence\Support\GooglePlacesNormalizer;

return static function (): void {
    $normalizer = new GooglePlacesNormalizer();
    assert_throws(fn () => $normalizer->normalize(['displayName' => ['text' => 'No ID']]), ProviderException::class, 'MAPS_PROVIDER_INVALID_RESPONSE');
    assert_throws(fn () => $normalizer->normalize(['id' => 'place-1']), ProviderException::class, 'MAPS_PROVIDER_INVALID_RESPONSE');
};
