<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2); $checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };
$controller = file_get_contents($root.'/app/Http/Controllers/Titan/WorkCoreSummaryController.php');
$view = file_get_contents($root.'/resources/views/titan/operations.blade.php');
$js = file_get_contents($root.'/public/js/titan-operations.js');
foreach (['invoices','payment_observations','trust_matters'] as $key) $expect(str_contains($controller, "'{$key}'"), "Summary table {$key} is missing.");
foreach (['finance','payments','trust_accounting'] as $key) $expect(str_contains($controller, "'{$key}'"), "Summary block {$key} is missing.");
foreach (['data-titan-finance-summary','data-titan-payment-summary','data-titan-trust-summary'] as $marker) $expect(str_contains($view, $marker), "Operations marker {$marker} is missing.");
foreach (['data-titan-finance-count','data-titan-payment-count','data-titan-trust-count'] as $marker) $expect(str_contains($js, $marker), "Operations renderer {$marker} is missing.");
$expect(str_contains($view, 'AUD'), 'Operations finance surface must identify the Australian-dollar default.');
$expect(str_contains($view, 'Payment providers do not own invoices'), 'ZeroPay authority warning is missing.');
$expect(str_contains($view, 'Trust funds are segregated'), 'Trust segregation warning is missing.');
fwrite(STDOUT, "Finance host surface: {$checks} checks passed.\n");
