<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** @return list<string> */
function titanZeroDependencyInputIssues(string $root): array
{
    $issues = [];

    $composerPath = $root . '/composer.json';
    $composer = json_decode((string) file_get_contents($composerPath), true);
    if (! is_array($composer)) {
        return ['composer.json is not valid JSON.'];
    }

    foreach (($composer['require'] ?? []) as $name => $constraint) {
        if (! is_string($constraint)) {
            continue;
        }
        if ($constraint === '@dev') {
            $issues[] = "Unbound Composer constraint: {$name}=@dev";
        }
        if (preg_match('/^v?\d+\.\d+\.\d+$/', $constraint) === 1) {
            $issues[] = "Exact Composer semver constraint: {$name}={$constraint}";
        }
    }

    foreach (($composer['repositories'] ?? []) as $repository) {
        if (($repository['type'] ?? null) !== 'path') {
            continue;
        }
        $relative = (string) ($repository['url'] ?? '');
        $path = $root . '/' . ltrim(preg_replace('#^\./#', '', $relative) ?? $relative, '/');
        if (! is_dir($path)) {
            $issues[] = "Missing Composer path repository: {$relative}";
            continue;
        }
        if (! is_file($path . '/composer.json')) {
            $issues[] = "Composer path repository has no composer.json: {$relative}";
        }
    }

    $packagePath = $root . '/package.json';
    $package = json_decode((string) file_get_contents($packagePath), true);
    if (! is_array($package)) {
        $issues[] = 'package.json is not valid JSON.';
        return $issues;
    }

    foreach (['dependencies', 'devDependencies', 'optionalDependencies'] as $section) {
        foreach (($package[$section] ?? []) as $name => $reference) {
            if (! is_string($reference) || ! str_starts_with($reference, 'file:')) {
                continue;
            }
            $relative = substr($reference, 5);
            $path = $root . '/' . ltrim(preg_replace('#^\./#', '', $relative) ?? $relative, '/');
            if (! file_exists($path)) {
                $issues[] = "Missing npm file dependency {$name}: {$relative}";
            }
        }
    }

    return $issues;
}

if (class_exists(TestCase::class)) {
    final class DependencyInputTest extends TestCase
    {
        public function test_all_local_dependency_inputs_exist(): void
        {
            self::assertSame([], titanZeroDependencyInputIssues(dirname(__DIR__, 2)));
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $issues = titanZeroDependencyInputIssues(dirname(__DIR__, 2));
    if ($issues !== []) {
        fwrite(STDERR, implode(PHP_EOL, $issues) . PHP_EOL);
        exit(1);
    }
    fwrite(STDOUT, "Dependency input test passed.\n");
}
