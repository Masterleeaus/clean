<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    $required = [
        'app/Extensions/TitanMapsIntelligence/Providers/LaravelProviderHttpTransport.php',
        'app/Extensions/TitanMapsIntelligence/Services/ExternalPlaceRepository.php',
        'app/Extensions/TitanMapsIntelligence/Services/DiscoverySearchService.php',
        'app/Extensions/TitanMapsIntelligence/Services/DiscoveryRunService.php',
        'app/Extensions/TitanMapsIntelligence/Jobs/ExecuteDiscoverySearch.php',
        'app/Extensions/TitanMapsIntelligence/Jobs/ProcessDiscoveryPage.php',
        'app/Extensions/TitanMapsIntelligence/Repositories/EloquentCandidateRepository.php',
        'app/Extensions/TitanMapsIntelligence/Http/Controllers/SearchController.php',
        'app/Extensions/TitanMapsIntelligence/Http/Controllers/CandidateController.php',
        'app/Extensions/TitanMapsIntelligence/Http/Controllers/TerritoryController.php',
        'app/Extensions/TitanMapsIntelligence/Http/Controllers/UsageController.php',
    ];
    foreach ($required as $file) {
        assert_true(is_file($root.'/'.$file), 'Missing integration surface: '.$file);
    }

    $transport = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Providers/LaravelProviderHttpTransport.php');
    assert_true(str_contains($transport, 'Http::acceptJson()'));
    assert_true(str_contains($transport, 'timeout('));
    assert_true(str_contains($transport, 'retry('));
    assert_true(str_contains($transport, "places.googleapis.com"));
    assert_true(! str_contains($transport, 'verify(false)'), 'TLS verification must not be disabled');
};
