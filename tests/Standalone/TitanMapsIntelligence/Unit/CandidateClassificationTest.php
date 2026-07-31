<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\CandidateClassificationService;

return static function (): void {
    $service = new CandidateClassificationService();
    assert_same('provider_candidate', $service->classify(['primary_category' => 'plumber', 'categories' => ['plumber']], 'provider_discovery')['type']);
    assert_same('accommodation_provider', $service->classify(['primary_category' => 'lodging', 'categories' => ['hotel', 'lodging']], 'accommodation_discovery')['type']);
    assert_same('competitor', $service->classify(['primary_category' => 'cleaning_service'], 'competitor_analysis')['type']);
    assert_same('unclassified', $service->classify([], 'unknown')['type']);
};
