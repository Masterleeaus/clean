<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

function titanZeroAuditWriteExtension(string $root, string $directory, string $name, string $provider, string $body): void
{
    $path = $root . '/' . $directory;
    mkdir($path . '/System', 0777, true);
    mkdir($path . '/database/migrations', 0777, true);
    file_put_contents($path . '/extension.json', json_encode([
        'name' => $name,
        'type' => 'extension',
        'version' => '1.0.0',
        'provider' => $provider,
    ], JSON_PRETTY_PRINT));
    $class = substr($provider, strrpos($provider, '\\') + 1);
    file_put_contents($path . '/System/' . $class . '.php', "<?php\nnamespace App\\Extensions\\{$directory}\\System;\nfinal class {$class} {}\n" . $body);
}

/** @return list<string> */
function titanZeroProviderAuditIssues(string $projectRoot): array
{
    foreach ([
        'ExtensionManifest.php', 'ExtensionCatalog.php', 'ExtensionProviderAuditResult.php', 'ExtensionProviderAudit.php',
    ] as $file) {
        $path = $projectRoot . '/app/Support/Extensions/' . $file;
        if (! is_file($path)) {
            return ["Missing provider audit implementation: {$file}"];
        }
        require_once $path;
    }

    $root = sys_get_temp_dir() . '/titan-zero-provider-audit-' . bin2hex(random_bytes(5));
    mkdir($root, 0777, true);
    $issues = [];

    try {
        titanZeroAuditWriteExtension($root, 'GreenOne', 'Green One', 'App\\Extensions\\GreenOne\\System\\GreenOneProvider', "\nfunction greenSignals(): void {}\n");
        titanZeroAuditWriteExtension($root, 'RedRouteOne', 'Red Route One', 'App\\Extensions\\RedRouteOne\\System\\RedRouteOneProvider', "\nfunction redRouteOne(\$route): void { \$route->name('shared.route'); }\n");
        titanZeroAuditWriteExtension($root, 'RedRouteTwo', 'Red Route Two', 'App\\Extensions\\RedRouteTwo\\System\\RedRouteTwoProvider', "\nfunction redRouteTwo(\$route): void { \$route->name('shared.route'); }\n");
        titanZeroAuditWriteExtension($root, 'AmberOne', 'Amber One', 'App\\Extensions\\AmberOne\\System\\AmberOneProvider', "\nfunction amberOne(\$router, \$app): void { \$router->aliasMiddleware('shared.alias', X::class); \$app->bind(SharedContract::class, Impl::class); Event::listen(SharedEvent::class, X::class); }\n");
        titanZeroAuditWriteExtension($root, 'AmberTwo', 'Amber Two', 'App\\Extensions\\AmberTwo\\System\\AmberTwoProvider', "\nfunction amberTwo(\$router, \$app): void { \$router->aliasMiddleware('shared.alias', Y::class); \$app->singleton(SharedContract::class, Impl::class); Event::listen(SharedEvent::class, Y::class); }\n");
        titanZeroAuditWriteExtension($root, 'MissingProvider', 'Missing Provider', 'App\\Extensions\\MissingProvider\\System\\AbsentProvider', '');
        unlink($root . '/MissingProvider/System/AbsentProvider.php');

        file_put_contents($root . '/RedRouteOne/database/migrations/a.php', "<?php class SharedMigration {}\n");
        file_put_contents($root . '/RedRouteTwo/database/migrations/b.php', "<?php class SharedMigration {}\n");

        $before = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $before[$file->getPathname()] = hash_file('sha256', $file->getPathname());
            }
        }

        $legacy = [];
        foreach (glob($root . '/*/extension.json') ?: [] as $manifestPath) {
            $data = json_decode((string) file_get_contents($manifestPath), true);
            $directory = basename(dirname($manifestPath));
            $legacy[strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $directory))] = $data['provider'];
        }

        $catalog = new \App\Support\Extensions\ExtensionCatalog($root, [], $legacy);
        $results = (new \App\Support\Extensions\ExtensionProviderAudit($catalog, $root))->audit();
        $byDirectory = [];
        foreach ($results as $result) {
            $byDirectory[$result->manifest->directory] = $result;
        }

        foreach (['GreenOne' => 'Green', 'RedRouteOne' => 'Red', 'RedRouteTwo' => 'Red', 'AmberOne' => 'Amber', 'AmberTwo' => 'Amber', 'MissingProvider' => 'Red'] as $directory => $expected) {
            $actual = $byDirectory[$directory]->status ?? null;
            if ($actual !== $expected) {
                $issues[] = "{$directory} status expected {$expected}, got " . ($actual ?? 'missing') . '.';
            }
        }

        $messages = implode("\n", array_merge(...array_map(static fn ($result) => $result->diagnostics, $results)));
        foreach (['Duplicate route name', 'Duplicate migration class', 'Duplicate middleware alias', 'Duplicate container binding', 'Duplicate event listener', 'Provider source is absent'] as $message) {
            if (! str_contains($messages, $message)) {
                $issues[] = "Missing audit diagnostic: {$message}";
            }
        }

        foreach ($before as $path => $hash) {
            if (! is_file($path) || hash_file('sha256', $path) !== $hash) {
                $issues[] = 'Audit mutated source: ' . $path;
            }
        }
    } finally {
        if (is_dir($root)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
            rmdir($root);
        }
    }

    if (! is_file($projectRoot . '/app/Console/Commands/TitanZeroProviderAuditCommand.php')) {
        $issues[] = 'Missing titan-zero:provider-audit command.';
    }
    $provider = (string) @file_get_contents($projectRoot . '/app/Providers/TitanZeroServiceProvider.php');
    if (! str_contains($provider, 'TitanZeroProviderAuditCommand::class')) {
        $issues[] = 'TitanZeroServiceProvider does not register the provider audit command.';
    }

    return $issues;
}

if (class_exists(TestCase::class)) {
    final class TitanZeroProviderAuditCommandTest extends TestCase
    {
        public function test_provider_audit_classifies_collisions_without_mutation(): void
        {
            self::assertSame([], titanZeroProviderAuditIssues(dirname(__DIR__, 3)));
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $issues = titanZeroProviderAuditIssues(dirname(__DIR__, 3));
    if ($issues !== []) {
        fwrite(STDERR, implode(PHP_EOL, $issues) . PHP_EOL);
        exit(1);
    }
    fwrite(STDOUT, "Provider audit test passed.\n");
}
