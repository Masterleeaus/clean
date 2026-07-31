<?php

declare(strict_types=1);

return static function (): void {
    $path = dirname(__DIR__, 4).'/app/Extensions/TitanMapsIntelligence/extension.json';
    assert_true(is_file($path), 'extension.json must exist');

    $manifest = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    assert_same('2.0', $manifest['schema_version'] ?? null);
    assert_same('titan-maps-intelligence', $manifest['id'] ?? null);
    assert_same('integration', $manifest['type'] ?? null);
    assert_same('App\\Extensions\\TitanMapsIntelligence\\TitanMapsIntelligenceServiceProvider', $manifest['provider'] ?? null);
    assert_true(($manifest['enabled_by_default'] ?? true) === false, 'Extension must default disabled');
    assert_true(($manifest['requires']['host'] ?? []) !== [], 'Host requirements must be declared');
    assert_true(in_array('crm', $manifest['requires']['workcore'] ?? [], true), 'WorkCore CRM requirement must be declared');

    $provides = $manifest['provides'] ?? [];
    $permissions = $manifest['permissions'] ?? [];
    assert_same(count($provides), count(array_unique($provides)), 'Capabilities must be unique');
    assert_same(count($permissions), count(array_unique($permissions)), 'Permissions must be unique');
    assert_true(in_array('titan-maps-intelligence.search.businesses', $provides, true), 'Search capability missing');
    assert_true(in_array('titan-maps-intelligence.candidate.promote', $permissions, true), 'Promotion permission missing');

    foreach ($manifest['settings'] ?? [] as $setting) {
        if (($setting['sensitive'] ?? false) === true) {
            assert_true(($setting['default'] ?? null) === null || ($setting['default'] ?? '') === '', 'Sensitive setting must not contain a default secret');
        }
    }
};
