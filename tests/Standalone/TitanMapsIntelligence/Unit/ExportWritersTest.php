<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Exports\CsvExportWriter;
use App\Extensions\TitanMapsIntelligence\Exports\JsonExportWriter;
use App\Extensions\TitanMapsIntelligence\Exports\XlsxExportWriter;

return static function (): void {
    $rows = [[
        'candidate_id' => 'c1', 'name' => 'Northside Plumbing', 'phone' => '0390000000',
        'provider' => 'google-places', 'source_url' => 'https://maps.example/place',
    ]];
    $csv = (new CsvExportWriter())->write($rows);
    assert_true(str_contains($csv, 'candidate_id,name,phone,provider,source_url'));
    assert_true(str_contains($csv, 'Northside Plumbing'));

    $json = (new JsonExportWriter())->write($rows);
    assert_same($rows, json_decode($json, true, flags: JSON_THROW_ON_ERROR));
    assert_true(! str_contains($json, 'raw_payload'));

    $xlsx = (new XlsxExportWriter())->write($rows);
    assert_true(str_starts_with($xlsx, 'PK'), 'XLSX must be a ZIP-based Open XML package');
    assert_true(strlen($xlsx) > 500);
};
