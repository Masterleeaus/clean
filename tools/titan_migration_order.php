<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = glob($root.'/database/migrations/*.php') ?: [];
sort($files, SORT_STRING);
$creates = [];
$references = [];

foreach ($files as $fileIndex => $file) {
    $source = (string) file_get_contents($file);
    if (preg_match_all('/Schema::create\([\'\"]([^\'\"]+)/', $source, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as [$table, $offset]) {
            $creates[$table] = [$fileIndex, basename($file), $offset];
        }
    }
    $patterns = [
        '/->constrained\([\'\"]([^\'\"]+)[\'\"]\)/s',
        '/->references\([^)]*\)->on\([\'\"]([^\'\"]+)[\'\"]\)/s',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as [$table, $offset]) {
                $references[] = [$fileIndex, basename($file), $table, $offset];
            }
        }
    }
    if (preg_match_all('/(?:foreignId|foreignUuid|foreignUlid)\([\'\"]([^\'\"]+)_id[\'\"]\)(?:(?!;).)*?->constrained\(\)/s', $source, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as [$base, $offset]) {
            if (preg_match('/[^aeiou]y$/', $base)) {
                $table = substr($base, 0, -1).'ies';
            } elseif (preg_match('/(?:s|x|z|ch|sh)$/', $base)) {
                $table = $base.'es';
            } else {
                $table = $base.'s';
            }
            $references[] = [$fileIndex, basename($file), $table, $offset];
        }
    }
}

$issues = [];
foreach ($references as [$fileIndex, $file, $table, $offset]) {
    if (! isset($creates[$table])) {
        $issues[] = "{$file}: references missing table {$table}";
        continue;
    }
    [$createFileIndex, $createFile, $createOffset] = $creates[$table];
    if ($createFileIndex > $fileIndex || ($createFileIndex === $fileIndex && $createOffset > $offset)) {
        $issues[] = "{$file}: references {$table} before it is created in {$createFile}";
    }
}

if ($issues !== []) {
    fwrite(STDERR, "Migration order scan failed:\n - ".implode("\n - ", array_unique($issues))."\n");
    exit(1);
}

printf(
    "Migration order scan: %d files, %d created tables, %d references, 0 issues\n",
    count($files),
    count($creates),
    count($references),
);
