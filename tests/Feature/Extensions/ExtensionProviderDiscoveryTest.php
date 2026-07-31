<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

function titanZeroRemoveDirectory(string $path): void
{
    if (! is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

/** @param array<string,mixed>|string $manifest */
function titanZeroWriteExtension(string $root, string $directory, array|string $manifest): void
{
    $path = $root . '/' . $directory;
    mkdir($path, 0777, true);
    $contents = is_array($manifest)
        ? json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        : $manifest;
    file_put_contents($path . '/extension.json', $contents);
}

/** @return list<string> */
function titanZeroExtensionDiscoveryIssues(string $projectRoot): array
{
    $required = [
        $projectRoot . '/app/Support/Extensions/ExtensionManifest.php',
        $projectRoot . '/app/Support/Extensions/ExtensionCatalog.php',
        $projectRoot . '/app/Support/Extensions/ExtensionProviderResolver.php',
        $projectRoot . '/app/Support/Extensions/CatalogExtensionProviderResolver.php',
    ];
    foreach ($required as $path) {
        if (! is_file($path)) {
            return ['Missing extension discovery implementation: ' . str_replace($projectRoot . '/', '', $path)];
        }
        require_once $path;
    }

    $issues = [];
    $root = sys_get_temp_dir() . '/titan-zero-extension-test-' . bin2hex(random_bytes(6));
    mkdir($root, 0777, true);

    try {
        titanZeroWriteExtension($root, 'BrokenJson', '{not-json');
        titanZeroWriteExtension($root, 'MissingProvider', [
            'name' => 'Missing Provider',
            'type' => 'extension',
            'version' => '1.0.0',
            'provider' => 'Tests\\MissingProvider',
        ]);
        titanZeroWriteExtension($root, 'DuplicateNameOne', [
            'name' => 'Duplicate Name',
            'type' => 'extension',
            'version' => '1.0.0',
            'provider' => 'Tests\\DuplicateNameOneProvider',
        ]);
        titanZeroWriteExtension($root, 'DuplicateNameTwo', [
            'name' => 'duplicate name',
            'type' => 'extension',
            'version' => '1.0.1',
            'provider' => 'Tests\\DuplicateNameTwoProvider',
        ]);
        titanZeroWriteExtension($root, 'DuplicateProviderOne', [
            'name' => 'Duplicate Provider One',
            'type' => 'extension',
            'version' => '1.0.0',
            'provider' => 'Tests\\SharedProvider',
        ]);
        titanZeroWriteExtension($root, 'DuplicateProviderTwo', [
            'name' => 'Duplicate Provider Two',
            'type' => 'extension',
            'version' => '1.0.0',
            'provider' => 'Tests\\SharedProvider',
        ]);
        titanZeroWriteExtension($root, 'DisabledValid', [
            'name' => 'Disabled Valid',
            'type' => 'extension',
            'version' => '1.0.0',
            'provider' => 'Tests\\DisabledProvider',
        ]);
        titanZeroWriteExtension($root, 'EnabledValid', [
            'name' => 'Enabled Valid',
            'type' => 'extension',
            'version' => '1.2.3',
            'provider' => 'Tests\\EnabledProvider',
        ]);
        titanZeroWriteExtension($root, 'LegacyFolder', [
            'name' => 'Legacy Extension',
            'type' => 'extension',
            'version' => '2.0',
        ]);

        $providerChecks = [];
        $catalog = new \App\Support\Extensions\ExtensionCatalog(
            extensionsPath: $root,
            enabledIdentifiers: [
                'broken-json',
                'missing-provider',
                'duplicate-name-one',
                'duplicate-name-two',
                'duplicate-provider-one',
                'duplicate-provider-two',
                'enabled-valid',
                'legacy-slug',
            ],
            legacyProviders: [
                'broken-json' => 'Tests\\BrokenJsonProvider',
                'missing-provider' => 'Tests\\MissingProvider',
                'duplicate-name-one' => 'Tests\\DuplicateNameOneProvider',
                'duplicate-name-two' => 'Tests\\DuplicateNameTwoProvider',
                'duplicate-provider-one' => 'Tests\\SharedProvider',
                'duplicate-provider-two' => 'Tests\\SharedProvider',
                'disabled-valid' => 'Tests\\DisabledProvider',
                'enabled-valid' => 'Tests\\EnabledProvider',
                'legacy-slug' => 'App\\Extensions\\LegacyFolder\\System\\LegacyServiceProvider',
            ],
        );
        $resolver = new \App\Support\Extensions\CatalogExtensionProviderResolver(
            catalog: $catalog,
            providerValidator: static function (string $provider) use (&$providerChecks): bool {
                $providerChecks[] = $provider;
                return in_array($provider, [
                    'Tests\\EnabledProvider',
                    'App\\Extensions\\LegacyFolder\\System\\LegacyServiceProvider',
                    'Tests\\DuplicateNameOneProvider',
                    'Tests\\DuplicateNameTwoProvider',
                    'Tests\\SharedProvider',
                ], true);
            },
        );

        $providers = $resolver->resolveEnabledProviders();
        $expectedProviders = [
            'Tests\\EnabledProvider',
            'App\\Extensions\\LegacyFolder\\System\\LegacyServiceProvider',
        ];
        sort($providers);
        sort($expectedProviders);
        if ($providers !== $expectedProviders) {
            $issues[] = 'Enabled provider resolution mismatch: ' . json_encode($providers);
        }

        if (in_array('Tests\\DisabledProvider', $providerChecks, true)) {
            $issues[] = 'Disabled extension provider was inspected or executed.';
        }

        $diagnostics = array_merge($catalog->issues(), $resolver->issues());
        foreach (['invalid JSON', 'provider class is not loadable', 'duplicate extension name', 'duplicate provider class'] as $expectedMessage) {
            if (! array_filter($diagnostics, static fn (string $message): bool => str_contains(strtolower($message), strtolower($expectedMessage)))) {
                $issues[] = "Missing discovery diagnostic containing: {$expectedMessage}";
            }
        }

        $legacy = array_values(array_filter(
            $catalog->manifests(),
            static fn (\App\Support\Extensions\ExtensionManifest $manifest): bool => $manifest->directory === 'LegacyFolder',
        ));
        if (count($legacy) !== 1 || $legacy[0]->provider !== 'App\\Extensions\\LegacyFolder\\System\\LegacyServiceProvider' || ! $legacy[0]->enabled) {
            $issues[] = 'Legacy provider fallback did not resolve the enabled legacy slug.';
        }
    } finally {
        titanZeroRemoveDirectory($root);
    }

    return $issues;
}

if (class_exists(TestCase::class)) {
    final class ExtensionProviderDiscoveryTest extends TestCase
    {
        public function test_catalog_qualifies_only_valid_explicitly_enabled_extensions(): void
        {
            self::assertSame([], titanZeroExtensionDiscoveryIssues(dirname(__DIR__, 3)));
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $issues = titanZeroExtensionDiscoveryIssues(dirname(__DIR__, 3));
    if ($issues !== []) {
        fwrite(STDERR, implode(PHP_EOL, $issues) . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, "Extension provider discovery test passed.\n");
}
