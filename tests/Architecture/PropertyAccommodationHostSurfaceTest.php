<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };

$controller = file_get_contents($root.'/app/Http/Controllers/Titan/WorkCoreSummaryController.php');
foreach (['agreements', 'occupancies', 'reservations', 'stays', 'housekeeping'] as $key) {
    $expect(str_contains($controller, "'{$key}'"), "Summary count {$key} is missing.");
}
foreach (['arrivals_today', 'departures_today', 'in_house', 'dirty_spaces', 'active_agreements', 'active_occupancies'] as $key) {
    $expect(str_contains($controller, "'{$key}'"), "Property/accommodation metric {$key} is missing.");
}
$view = file_get_contents($root.'/resources/views/titan/operations.blade.php');
$expect(str_contains($view, 'WorkCore Property & Accommodation'), 'Operations property/accommodation panel is missing.');
$expect(str_contains($view, 'data-titan-accommodation-count'), 'Operations accommodation metrics are not data-bound.');
$js = file_get_contents($root.'/public/js/titan-operations.js');
$expect(str_contains($js, 'data-titan-accommodation-count'), 'Operations JS does not render accommodation metrics.');
$expect(is_file($root.'/docs/integration/PROPERTY_ACCOMMODATION_DELTA.md'), 'Property/accommodation delta report is missing.');

fwrite(STDOUT, "Property/accommodation host surface: {$checks} checks passed.\n");
