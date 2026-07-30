<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

function titanZeroProjectRoot(): string
{
    return dirname(__DIR__, 2);
}

/** @return list<string> */
function titanZeroSourceBaselineIssues(string $root): array
{
    $issues = [];
    $required = [
        'artisan',
        'composer.json',
        'package.json',
        'app/Domains/WorkCore/WorkCoreServiceProvider.php',
        'app/Extensions/Chatbot/extension.json',
        'EXTENSIONS_IMPORT_MANIFEST.json',
        'tools/titan-zero-audit/baseline.php',
    ];

    foreach ($required as $path) {
        if (! is_file($root . '/' . $path)) {
            $issues[] = "Missing required file: {$path}";
        }
    }

    $manifestPath = $root . '/EXTENSIONS_IMPORT_MANIFEST.json';
    if (is_file($manifestPath)) {
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            $issues[] = 'EXTENSIONS_IMPORT_MANIFEST.json is not valid JSON.';
        } else {
            $expected = (int) ($manifest['extension_directories'] ?? 0);
            $actual = glob($root . '/app/Extensions/*/extension.json') ?: [];
            if ($expected !== 104) {
                $issues[] = "Expected canonical manifest count 104; found {$expected}.";
            }
            if (count($actual) !== $expected) {
                $issues[] = sprintf('Top-level extension manifests mismatch: expected %d, found %d.', $expected, count($actual));
            }
        }
    }

    $outputPath = $root . '/storage/app/audits/source-baseline.json';
    if (! is_file($outputPath)) {
        $issues[] = 'Baseline audit output has not been generated.';
    } else {
        $output = json_decode((string) file_get_contents($outputPath), true);
        if (! is_array($output)) {
            $issues[] = 'Baseline audit output is not valid JSON.';
        } elseif (($output['summary']['required_paths_ok'] ?? false) !== true) {
            $issues[] = 'Baseline audit did not confirm required paths.';
        }
    }

    return $issues;
}

if (class_exists(TestCase::class)) {
    final class SourceBaselineTest extends TestCase
    {
        public function test_imported_source_matches_the_canonical_baseline(): void
        {
            self::assertSame([], titanZeroSourceBaselineIssues(titanZeroProjectRoot()));
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $issues = titanZeroSourceBaselineIssues(titanZeroProjectRoot());
    if ($issues !== []) {
        fwrite(STDERR, implode(PHP_EOL, $issues) . PHP_EOL);
        exit(1);
    }
    fwrite(STDOUT, "Source baseline test passed.\n");
}
