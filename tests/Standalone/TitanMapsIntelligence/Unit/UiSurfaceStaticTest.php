<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    foreach (['search-composer','search-progress-card','candidate-card','territory-summary'] as $component) {
        assert_true(is_file($root.'/app/Extensions/TitanMapsIntelligence/resources/views/components/'.$component.'.blade.php'), 'Missing Meetup card component '.$component);
    }
    assert_true(is_file($root.'/app/Extensions/TitanMapsIntelligence/resources/js/titan-maps-intelligence.js'));
    $provider = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/TitanMapsIntelligenceServiceProvider.php');
    assert_true(str_contains($provider, 'loadViewsFrom'));
};
