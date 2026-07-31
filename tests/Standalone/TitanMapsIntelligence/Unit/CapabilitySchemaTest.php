<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Services\MapsCapabilityService;

return static function (): void {
    $manifest = json_decode((string) file_get_contents(dirname(__DIR__, 4).'/app/Extensions/TitanMapsIntelligence/extension.json'), true, flags: JSON_THROW_ON_ERROR);
    $definitions = (new MapsCapabilityService())->definitions();
    assert_same(count($manifest['provides']), count($definitions));
    $ids = array_column($definitions, 'id');
    sort($ids);
    $provided = $manifest['provides'];
    sort($provided);
    assert_same($provided, $ids);

    $byId = array_column($definitions, null, 'id');
    assert_same(['candidate_id', 'workcore_entity_type', 'workcore_entity_id'], $byId['titan-maps-intelligence.candidate.match']['input_schema']['required']);
    assert_true(isset($byId['titan-maps-intelligence.search.businesses']['input_schema']['properties']['radius_metres']));
    assert_true(isset($byId['titan-maps-intelligence.search.businesses']['input_schema']['properties']['open_now']));
    assert_true(isset($byId['titan-maps-intelligence.candidate.promote']['input_schema']['properties']['accepted_fields']));

    foreach ($definitions as $definition) {
        assert_true(str_starts_with($definition['permission'], 'titan-maps-intelligence.'), 'Capability permission must be namespaced');
        assert_same('object', $definition['input_schema']['type'] ?? null);
        assert_same('object', $definition['output_schema']['type'] ?? null);
        assert_true(! str_contains(json_encode($definition, JSON_THROW_ON_ERROR), 'raw_payload'), 'Tools must not expose raw provider payloads');
    }
};
