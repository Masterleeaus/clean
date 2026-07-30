<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

$files = glob(__DIR__.'/Unit/*Test.php') ?: [];
$passed = 0;
$failed = 0;

foreach ($files as $file) {
    $name = basename($file, '.php');
    try {
        $test = require $file;
        $test();
        echo "PASS {$name}\n";
        $passed++;
    } catch (Throwable $throwable) {
        echo "FAIL {$name}: {$throwable->getMessage()}\n";
        $failed++;
    }
}

echo "\n{$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
