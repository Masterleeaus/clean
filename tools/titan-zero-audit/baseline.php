<?php

declare(strict_types=1);

final class TitanZeroBaselineAudit
{
    private const OVERSIZED_FILE_BYTES = 1_048_576;

    /** @var list<string> */
    private const REQUIRED_PATHS = [
        'artisan',
        'composer.json',
        'package.json',
        'app/Domains/WorkCore/WorkCoreServiceProvider.php',
        'app/Extensions/Chatbot/extension.json',
        'EXTENSIONS_IMPORT_MANIFEST.json',
    ];

    public function __construct(private readonly string $root)
    {
    }

    /** @return array<string, mixed> */
    public function run(): array
    {
        $requiredPaths = $this->requiredPaths();
        $extensions = $this->extensionInventory();
        $composerPaths = $this->composerPathRepositories();
        $npmFileDependencies = $this->npmFileDependencies();
        $phpSymbols = $this->duplicatePhpSymbols();
        $oversizedFiles = $this->oversizedFiles();

        return [
            'generated_at' => gmdate(DATE_ATOM),
            'root' => $this->root,
            'summary' => [
                'required_paths_ok' => $requiredPaths['missing'] === [],
                'extension_inventory_ok' => $extensions['missing'] === [] && $extensions['extra'] === [],
                'expected_extension_directories' => $extensions['expected_count'],
                'actual_extension_directories' => $extensions['actual_count'],
                'duplicate_php_symbol_count' => count($phpSymbols),
                'oversized_file_count' => count($oversizedFiles),
                'missing_composer_path_count' => count($composerPaths['missing']),
                'missing_npm_file_dependency_count' => count($npmFileDependencies['missing']),
            ],
            'required_paths' => $requiredPaths,
            'extensions' => $extensions,
            'composer_path_repositories' => $composerPaths,
            'npm_file_dependencies' => $npmFileDependencies,
            'duplicate_php_symbols' => $phpSymbols,
            'oversized_files' => $oversizedFiles,
        ];
    }

    /** @return array{present:list<string>,missing:list<string>} */
    private function requiredPaths(): array
    {
        $present = [];
        $missing = [];

        foreach (self::REQUIRED_PATHS as $relative) {
            if (file_exists($this->path($relative))) {
                $present[] = $relative;
            } else {
                $missing[] = $relative;
            }
        }

        return compact('present', 'missing');
    }

    /** @return array<string, mixed> */
    private function extensionInventory(): array
    {
        $manifest = $this->decodeJsonFile($this->path('EXTENSIONS_IMPORT_MANIFEST.json'));
        $expected = [];
        foreach (($manifest['records'] ?? []) as $record) {
            $namespace = trim((string) ($record['namespace'] ?? ''));
            if ($namespace !== '') {
                $expected[$namespace] = true;
            }
        }

        $declaredCount = (int) ($manifest['extension_directories'] ?? count($expected));
        $actual = [];
        foreach (glob($this->path('app/Extensions/*/extension.json')) ?: [] as $extensionManifest) {
            $actual[basename(dirname($extensionManifest))] = true;
        }

        $expectedNames = array_keys($expected);
        $actualNames = array_keys($actual);
        sort($expectedNames);
        sort($actualNames);

        return [
            'declared_count' => $declaredCount,
            'expected_count' => count($expectedNames),
            'actual_count' => count($actualNames),
            'missing' => array_values(array_diff($expectedNames, $actualNames)),
            'extra' => array_values(array_diff($actualNames, $expectedNames)),
            'expected' => $expectedNames,
            'actual' => $actualNames,
        ];
    }

    /** @return array{configured:list<array<string,mixed>>,missing:list<string>} */
    private function composerPathRepositories(): array
    {
        $composer = $this->decodeJsonFile($this->path('composer.json'));
        $configured = [];
        $missing = [];

        foreach (($composer['repositories'] ?? []) as $repository) {
            if (($repository['type'] ?? null) !== 'path') {
                continue;
            }
            $relative = (string) ($repository['url'] ?? '');
            $relativePath = ltrim((string) preg_replace('#^\./#', '', $relative), '/');
            $directory = $this->path($relativePath);
            $exists = is_dir($directory);
            $hasManifest = is_file($directory . '/composer.json');
            $configured[] = ['path' => $relative, 'exists' => $exists, 'has_composer_json' => $hasManifest];
            if (! $exists || ! $hasManifest) {
                $missing[] = $relative;
            }
        }

        return compact('configured', 'missing');
    }

    /** @return array{configured:list<array<string,mixed>>,missing:list<string>} */
    private function npmFileDependencies(): array
    {
        $package = $this->decodeJsonFile($this->path('package.json'));
        $configured = [];
        $missing = [];

        foreach (['dependencies', 'devDependencies', 'optionalDependencies'] as $section) {
            foreach (($package[$section] ?? []) as $name => $reference) {
                if (! is_string($reference) || ! str_starts_with($reference, 'file:')) {
                    continue;
                }
                $relative = substr($reference, 5);
                $relativePath = ltrim((string) preg_replace('#^\./#', '', $relative), '/');
                $exists = file_exists($this->path($relativePath));
                $configured[] = ['section' => $section, 'name' => (string) $name, 'path' => $relative, 'exists' => $exists];
                if (! $exists) {
                    $missing[] = "{$name}:{$relative}";
                }
            }
        }

        return compact('configured', 'missing');
    }

    /** @return list<array{symbol:string,files:list<string>}> */
    private function duplicatePhpSymbols(): array
    {
        $symbols = [];
        foreach ($this->phpFiles() as $file) {
            foreach ($this->symbolsInFile($file) as $symbol) {
                $symbols[$symbol][] = $this->relative($file);
            }
        }

        $duplicates = [];
        foreach ($symbols as $symbol => $files) {
            $files = array_values(array_unique($files));
            if (count($files) > 1) {
                sort($files);
                $duplicates[] = ['symbol' => $symbol, 'files' => $files];
            }
        }
        usort($duplicates, static fn (array $a, array $b): int => strcmp($a['symbol'], $b['symbol']));

        return $duplicates;
    }

    /** @return iterable<string> */
    private function phpFiles(): iterable
    {
        foreach (['app', 'config', 'database', 'routes'] as $relative) {
            $directory = $this->path($relative);
            if (! is_dir($directory)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && strtolower($file->getExtension()) === 'php') {
                    yield $file->getPathname();
                }
            }
        }
    }

    /** @return list<string> */
    private function symbolsInFile(string $file): array
    {
        $contents = @file_get_contents($file);
        if (! is_string($contents) || $contents === '') {
            return [];
        }

        $tokens = token_get_all($contents);
        $namespace = '';
        $symbols = [];
        $count = count($tokens);
        for ($index = 0; $index < $count; $index++) {
            $token = $tokens[$index];
            if (! is_array($token)) {
                continue;
            }
            if ($token[0] === T_NAMESPACE) {
                $namespace = '';
                for ($cursor = $index + 1; $cursor < $count; $cursor++) {
                    $part = $tokens[$cursor];
                    if ($part === ';' || $part === '{') {
                        break;
                    }
                    if (is_array($part) && in_array($part[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                        $namespace .= $part[1];
                    }
                }
                continue;
            }

            $classTokens = [T_CLASS, T_INTERFACE, T_TRAIT];
            if (defined('T_ENUM')) {
                $classTokens[] = T_ENUM;
            }
            if (! in_array($token[0], $classTokens, true)) {
                continue;
            }
            $previous = $this->previousSignificantToken($tokens, $index);
            if (is_array($previous) && in_array($previous[0], [T_NEW, T_DOUBLE_COLON], true)) {
                continue;
            }
            for ($cursor = $index + 1; $cursor < $count; $cursor++) {
                $next = $tokens[$cursor];
                if (is_array($next) && $next[0] === T_STRING) {
                    $symbols[] = ltrim($namespace . '\\' . $next[1], '\\');
                    break;
                }
                if ($next === '{' || $next === '(') {
                    break;
                }
            }
        }

        return array_values(array_unique($symbols));
    }

    private function previousSignificantToken(array $tokens, int $index): mixed
    {
        for ($cursor = $index - 1; $cursor >= 0; $cursor--) {
            $token = $tokens[$cursor];
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            return $token;
        }
        return null;
    }

    /** @return list<array{path:string,bytes:int}> */
    private function oversizedFiles(): array
    {
        $results = [];
        foreach (['app', 'config', 'database', 'resources', 'routes'] as $relative) {
            $directory = $this->path($relative);
            if (! is_dir($directory)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && $file->getSize() > self::OVERSIZED_FILE_BYTES) {
                    $results[] = ['path' => $this->relative($file->getPathname()), 'bytes' => $file->getSize()];
                }
            }
        }
        usort($results, static fn (array $a, array $b): int => $b['bytes'] <=> $a['bytes']);
        return $results;
    }

    /** @return array<string,mixed> */
    private function decodeJsonFile(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function path(string $relative): string
    {
        return $this->root . '/' . ltrim($relative, '/');
    }

    private function relative(string $path): string
    {
        return ltrim(str_replace($this->root, '', $path), DIRECTORY_SEPARATOR);
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 2);
    $audit = (new TitanZeroBaselineAudit($root))->run();
    $outputDirectory = $root . '/storage/app/audits';
    if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0775, true) && ! is_dir($outputDirectory)) {
        fwrite(STDERR, "Unable to create audit output directory.\n");
        exit(1);
    }
    file_put_contents($outputDirectory . '/source-baseline.json', json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    fwrite(STDOUT, sprintf(
        "Titan Zero baseline: required=%s extensions=%d/%d duplicate-symbols=%d oversized=%d missing-composer-paths=%d missing-npm-files=%d\n",
        $audit['summary']['required_paths_ok'] ? 'ok' : 'missing',
        $audit['summary']['actual_extension_directories'],
        $audit['summary']['expected_extension_directories'],
        $audit['summary']['duplicate_php_symbol_count'],
        $audit['summary']['oversized_file_count'],
        $audit['summary']['missing_composer_path_count'],
        $audit['summary']['missing_npm_file_dependency_count'],
    ));
    exit(($audit['summary']['required_paths_ok'] && $audit['summary']['extension_inventory_ok']) ? 0 : 1);
}
