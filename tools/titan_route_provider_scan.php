<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = array_merge(
    [$root.'/bootstrap/app.php', $root.'/bootstrap/providers.php'],
    glob($root.'/routes/*.php') ?: [],
);
$classes = [];

foreach ($files as $file) {
    $source = (string) file_get_contents($file);
    if (preg_match_all('/(?:use\s+|\b)(App\\\\[A-Za-z0-9_\\\\]+)(?:\s+as\s+[A-Za-z0-9_]+)?\s*;/', $source, $matches)) {
        foreach ($matches[1] as $class) {
            $classes[$class] = basename($file);
        }
    }
    if (basename($file) === 'providers.php' && preg_match_all('/(App\\\\[A-Za-z0-9_\\\\]+)::class/', $source, $matches)) {
        foreach ($matches[1] as $class) {
            $classes[$class] = basename($file);
        }
    }
}

$issues = [];
foreach ($classes as $class => $sourceFile) {
    $relative = substr($class, 4);
    $path = $root.'/app/'.str_replace('\\', '/', $relative).'.php';
    if (! is_file($path)) {
        $issues[] = "{$sourceFile}: {$class} does not resolve to {$path}";
    }
}

if ($issues !== []) {
    fwrite(STDERR, "Route/provider scan failed:\n - ".implode("\n - ", $issues)."\n");
    exit(1);
}

printf("Route/provider scan: %d files, %d App classes, 0 unresolved\n", count($files), count($classes));
