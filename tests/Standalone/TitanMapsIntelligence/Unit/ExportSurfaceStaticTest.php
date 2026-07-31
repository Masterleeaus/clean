<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    foreach (['app/Extensions/TitanMapsIntelligence/Contracts/PrivateExportStore.php','app/Extensions/TitanMapsIntelligence/Services/CandidateExportService.php','app/Extensions/TitanMapsIntelligence/Http/Controllers/ExportController.php','app/Extensions/TitanMapsIntelligence/Tools/ExportSearchTool.php'] as $file) {
        assert_true(is_file($root.'/'.$file), 'Missing export surface '.$file);
    }
    $routes = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/routes/api.php');
    assert_true(str_contains($routes, 'ExportController'));
    assert_true(str_contains($routes, '/searches/{search}/export'));
    $manifest = json_decode((string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/extension.json'), true, flags: JSON_THROW_ON_ERROR);
    assert_true(in_array('titan-maps-intelligence.search.export', $manifest['provides'], true));
};
