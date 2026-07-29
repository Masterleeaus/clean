<?php

declare(strict_types=1);

namespace App\Support\Extensions;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ExtensionProviderAudit
{
    /** @param list<string> $hostSourcePaths */
    public function __construct(
        private readonly ExtensionCatalog $catalog,
        private readonly string $extensionsPath,
        private readonly array $hostSourcePaths = [],
    ) {
    }

    /** @return list<ExtensionProviderAuditResult> */
    public function audit(): array
    {
        $manifests = $this->catalog->manifests();
        $diagnostics = [];
        $severity = [];

        foreach ($manifests as $manifest) {
            $diagnostics[$manifest->directory] = [];
            $severity[$manifest->directory] = 'Green';

            if (! $this->catalog->isValid($manifest)) {
                $this->add($diagnostics, $severity, $manifest->directory, 'Red', 'Manifest/catalog validation failed.');
            }
            if ($manifest->provider === null) {
                $this->add($diagnostics, $severity, $manifest->directory, 'Red', 'No provider class is declared or mapped.');
            } elseif (! $this->providerSourceExists($manifest)) {
                $this->add($diagnostics, $severity, $manifest->directory, 'Red', "Provider source is absent [{$manifest->provider}].");
            }
        }

        foreach ($this->catalog->issues() as $issue) {
            foreach ($manifests as $manifest) {
                if (str_contains($issue, $manifest->directory)) {
                    $this->add($diagnostics, $severity, $manifest->directory, 'Red', $issue);
                }
            }
        }

        $signals = [
            'route name' => ['severity' => 'Red', 'values' => []],
            'migration class' => ['severity' => 'Red', 'values' => []],
            'middleware alias' => ['severity' => 'Amber', 'values' => []],
            'container binding' => ['severity' => 'Amber', 'values' => []],
            'event listener' => ['severity' => 'Amber', 'values' => []],
        ];

        foreach ($manifests as $manifest) {
            $directory = rtrim($this->extensionsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $manifest->directory;
            foreach ($this->phpFiles($directory) as $file) {
                $contents = (string) file_get_contents($file);
                foreach ($this->signalsInFile($file, $contents) as $type => $values) {
                    foreach ($values as $value) {
                        $signals[$type]['values'][$value][$manifest->directory] = true;
                    }
                }
            }
        }

        foreach ($this->hostSourcePaths as $hostPath) {
            foreach ($this->phpFiles($hostPath) as $file) {
                $contents = (string) file_get_contents($file);
                foreach ($this->signalsInFile($file, $contents) as $type => $values) {
                    foreach ($values as $value) {
                        $signals[$type]['values'][$value]['@host'] = true;
                    }
                }
            }
        }

        foreach ($signals as $type => $signal) {
            foreach ($signal['values'] as $value => $owners) {
                if (count($owners) < 2) {
                    continue;
                }
                $ownerNames = array_keys($owners);
                sort($ownerNames);
                foreach ($ownerNames as $owner) {
                    if ($owner === '@host') {
                        continue;
                    }
                    $this->add(
                        $diagnostics,
                        $severity,
                        $owner,
                        $signal['severity'],
                        sprintf('Duplicate %s [%s] across %s.', $type, $value, implode(', ', $ownerNames)),
                    );
                }
            }
        }

        $results = [];
        foreach ($manifests as $manifest) {
            $items = array_values(array_unique($diagnostics[$manifest->directory] ?? []));
            sort($items);
            $results[] = new ExtensionProviderAuditResult(
                manifest: $manifest,
                status: $severity[$manifest->directory] ?? 'Green',
                diagnostics: $items,
            );
        }
        usort($results, static fn (ExtensionProviderAuditResult $a, ExtensionProviderAuditResult $b): int => strcmp($a->manifest->directory, $b->manifest->directory));

        return $results;
    }

    /** @return iterable<string> */
    private function phpFiles(string $path): iterable
    {
        if (is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php') {
            yield $path;
            return;
        }
        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && strtolower($file->getExtension()) === 'php') {
                yield $file->getPathname();
            }
        }
    }

    /** @return array<string,list<string>> */
    private function signalsInFile(string $file, string $contents): array
    {
        $contents = $this->withoutComments($contents);
        $signals = [
            'route name' => $this->matches('/->name\(\s*[\'\"]([^\'\"]+)[\'\"]\s*\)/', $contents),
            'middleware alias' => $this->matches('/aliasMiddleware\(\s*[\'\"]([^\'\"]+)[\'\"]/', $contents),
            'container binding' => $this->matches('/(?:bind|singleton|scoped)\(\s*([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)::class/', $contents),
            'event listener' => $this->matches('/(?:Event::listen|->listen)\(\s*([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)::class/', $contents),
            'migration class' => [],
        ];

        if (str_contains(str_replace('\\', '/', $file), '/database/migrations/')) {
            $signals['migration class'] = $this->namedClasses($contents);
        }

        return $signals;
    }

    private function withoutComments(string $contents): string
    {
        $clean = '';
        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $clean .= is_array($token) ? $token[1] : $token;
        }
        return $clean;
    }

    /** @return list<string> */
    private function matches(string $pattern, string $contents): array
    {
        preg_match_all($pattern, $contents, $matches);
        $values = array_values(array_unique(array_filter(array_map('trim', $matches[1] ?? []))));
        sort($values);
        return $values;
    }

    /** @return list<string> */
    private function namedClasses(string $contents): array
    {
        $classes = [];
        $tokens = token_get_all($contents);
        $count = count($tokens);
        for ($index = 0; $index < $count; $index++) {
            $token = $tokens[$index];
            if (! is_array($token) || $token[0] !== T_CLASS) {
                continue;
            }
            for ($cursor = $index + 1; $cursor < $count; $cursor++) {
                $next = $tokens[$cursor];
                if (is_array($next) && $next[0] === T_STRING) {
                    $classes[] = $next[1];
                    break;
                }
                if ($next === '(' || $next === '{') {
                    break;
                }
            }
        }
        $classes = array_values(array_unique($classes));
        sort($classes);
        return $classes;
    }

    private function providerSourceExists(ExtensionManifest $manifest): bool
    {
        if ($manifest->provider === null) {
            return false;
        }
        $prefix = 'App\\Extensions\\' . $manifest->directory . '\\';
        if (! str_starts_with($manifest->provider, $prefix)) {
            return false;
        }
        $relative = substr($manifest->provider, strlen($prefix));
        return is_file(rtrim($this->extensionsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $manifest->directory . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php');
    }

    /** @param array<string,list<string>> $diagnostics @param array<string,string> $severity */
    private function add(array &$diagnostics, array &$severity, string $directory, string $level, string $message): void
    {
        $diagnostics[$directory][] = $message;
        $rank = ['Green' => 0, 'Amber' => 1, 'Red' => 2];
        if (($rank[$level] ?? 0) > ($rank[$severity[$directory] ?? 'Green'] ?? 0)) {
            $severity[$directory] = $level;
        }
    }
}
