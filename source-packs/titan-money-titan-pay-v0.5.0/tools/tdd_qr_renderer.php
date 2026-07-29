<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$script = $root.'/resources/python/titanpay_qr/generate.py';
if (! is_file($script)) {
    fwrite(STDERR, "FAIL: bundled QR renderer missing\n");
    exit(1);
}

$payload = 'https://example.test/pay/'.str_repeat('A', 64);

// Hard-code python3 to eliminate environment variable injection risks
$pythonBinary = 'python3';

$descriptorspec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$process = proc_open([$pythonBinary, $script, $payload], $descriptorspec, $pipes);

if (! is_resource($process)) {
    fwrite(STDERR, "FAIL: unable to execute QR renderer\n");
    exit(1);
}

fclose($pipes[0]);
$output = fread($pipes[1], 2097152);
$errors = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$status = proc_close($process);

if ($status !== 0) {
    fwrite(STDERR, "FAIL: QR renderer exited with status {$status}\n");
    if ($errors) fwrite(STDERR, "Error: {$errors}\n");
    exit(1);
}

if (! is_string($output) || ! str_contains($output, '<svg') || ! str_contains($output, '<path')) {
    fwrite(STDERR, "FAIL: bundled QR renderer did not produce an SVG path\n");
    exit(1);
}
if (strlen($output) < 1000 || strlen($output) > 2000000) {
    fwrite(STDERR, "FAIL: QR SVG size is outside safe bounds\n");
    exit(1);
}

echo "GREEN: bundled QR renderer produced a scannable SVG document.\n";
