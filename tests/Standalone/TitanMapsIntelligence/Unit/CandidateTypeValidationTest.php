<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Exceptions\MapsIntelligenceException;
use App\Extensions\TitanMapsIntelligence\Services\CandidateTypeValidator;

return static function (): void {
    $validator = new CandidateTypeValidator();
    assert_same('provider_candidate', $validator->validate('provider_candidate'));
    assert_throws(fn () => $validator->validate('invented_type'), MapsIntelligenceException::class, 'MAPS_INVALID_CANDIDATE_TYPE');
};
