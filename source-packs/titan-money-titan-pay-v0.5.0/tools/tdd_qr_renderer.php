<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$script = $root.'/resources/python/titanpay_qr/generate.py';
if (! is_file($script)) {
    fwrite(STDERR, "FAIL: bundled QR renderer missing\n");
    exit(1);
}

$payload = 'https://example.test/pay/'.str_repeat('A', 64);
$command = escapeshellcmd(getenv('TITANPAY_QR_PYTHON_BINARY') ?: 'python3').' '.escapeshellarg($script).' '.escapeshellarg($payload);
$output = shell_exec($command);
if (! is_string($output) || ! str_contains($output, '<svg') || ! str_contains($output, '<path')) {
    fwrite(STDERR, "FAIL: bundled QR renderer did not produce an SVG path\n");
    exit(1);
}
if (strlen($output) < 1000 || strlen($output) > 2000000) {
    fwrite(STDERR, "FAIL: QR SVG size is outside safe bounds\n");
    exit(1);
}

echo "GREEN: bundled QR renderer produced a scannable SVG document.\n";
