<?php

declare(strict_types=1);

/**
 * Generate deterministic inventories and a collision ledger for the
 * Titan Money + Titan Pay staged-source integration.
 *
 * Usage:
 *   php tools/titan-money-pay-inventory.php
 */

$root = dirname(__DIR__);
$stagedRoot = $root.'/source-packs/titan-money-titan-pay-v0.5.0';
$outputRoot = $root.'/docs/integration/titan-money-titan-pay';

if (! is_dir($stagedRoot)) {
    fwrite(STDERR, "Staged source is missing: {$stagedRoot}\n");
    exit(2);
}

if (! is_dir($outputRoot) && ! mkdir($outputRoot, 0775, true) && ! is_dir($outputRoot)) {
    throw new RuntimeException("Unable to create output directory: {$outputRoot}");
}

/** @return array<string, array{path:string,bytes:int,sha256:string,extension:string}> */
function inventory(string $root, array $excludedPrefixes = []): array
{
    $root = rtrim(str_replace('\\', '/', realpath($root) ?: $root), '/');
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! $file->isFile()) {
            continue;
        }

        $absolute = str_replace('\\', '/', $file->getPathname());
        $relative = ltrim(substr($absolute, strlen($root)), '/');
        $skip = false;
        foreach ($excludedPrefixes as $prefix) {
            if ($relative === $prefix || str_starts_with($relative, rtrim($prefix, '/').'/')) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }

        $files[$relative] = [
            'path' => $relative,
            'bytes' => $file->getSize(),
            'sha256' => hash_file('sha256', $absolute),
            'extension' => strtolower(pathinfo($relative, PATHINFO_EXTENSION)),
        ];
    }

    ksort($files, SORT_STRING);
    return $files;
}

function writeJson(string $path, mixed $value): void
{
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    if (file_put_contents($path, $json) === false) {
        throw new RuntimeException("Unable to write {$path}");
    }
}

$base = inventory($root, [
    '.git',
    'vendor',
    'node_modules',
    'public/build',
    'storage/logs',
    'storage/framework',
    'source-packs',
    'docs/integration/titan-money-titan-pay/base-inventory.json',
    'docs/integration/titan-money-titan-pay/staged-source-inventory.json',
    'docs/integration/titan-money-titan-pay/path-collision-ledger.md',
]);
$staged = inventory($stagedRoot, ['vendor', 'node_modules', 'public/build', 'storage/logs']);

$collisions = [];
foreach ($staged as $path => $source) {
    if (! isset($base[$path])) {
        continue;
    }

    $target = $base[$path];
    $collisions[] = [
        'path' => $path,
        'same_content' => hash_equals($source['sha256'], $target['sha256']),
        'base_sha256' => $target['sha256'],
        'staged_sha256' => $source['sha256'],
        'base_bytes' => $target['bytes'],
        'staged_bytes' => $source['bytes'],
    ];
}

usort($collisions, static fn (array $a, array $b): int => strcmp($a['path'], $b['path']));

writeJson($outputRoot.'/base-inventory.json', [
    'generated_at' => gmdate(DATE_ATOM),
    'root' => '.',
    'file_count' => count($base),
    'files' => array_values($base),
]);
writeJson($outputRoot.'/staged-source-inventory.json', [
    'generated_at' => gmdate(DATE_ATOM),
    'root' => 'source-packs/titan-money-titan-pay-v0.5.0',
    'file_count' => count($staged),
    'files' => array_values($staged),
]);

$lines = [
    '# Titan Money + Titan Pay Path-Collision Ledger',
    '',
    '- Base files: `'.count($base).'`',
    '- Staged files: `'.count($staged).'`',
    '- Colliding relative paths: `'.count($collisions).'`',
    '- Identical collisions: `'.count(array_filter($collisions, static fn (array $row): bool => $row['same_content'])).'`',
    '- Divergent collisions: `'.count(array_filter($collisions, static fn (array $row): bool => ! $row['same_content'])).'`',
    '',
    '| Path | Result | Base bytes | Staged bytes |',
    '|---|---:|---:|---:|',
];
foreach ($collisions as $collision) {
    $lines[] = sprintf(
        '| `%s` | %s | %d | %d |',
        str_replace('|', '\\|', $collision['path']),
        $collision['same_content'] ? 'identical' : 'review-required',
        $collision['base_bytes'],
        $collision['staged_bytes'],
    );
}
$lines[] = '';
$lines[] = 'A collision is not permission to overwrite. Every divergent path requires an explicit disposition under the root upgrade plan.';

if (file_put_contents($outputRoot.'/path-collision-ledger.md', implode("\n", $lines)."\n") === false) {
    throw new RuntimeException('Unable to write path-collision-ledger.md');
}

printf(
    "Generated inventories: base=%d staged=%d collisions=%d divergent=%d\n",
    count($base),
    count($staged),
    count($collisions),
    count(array_filter($collisions, static fn (array $row): bool => ! $row['same_content'])),
);
