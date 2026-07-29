<?php

declare(strict_types=1);

namespace App\Support\Extensions;

use JsonException;

final class ExtensionCatalog
{
    /** @var list<ExtensionManifest>|null */
    private ?array $manifests = null;

    /** @var list<string> */
    private array $issues = [];

    /** @var array<string, true> */
    private array $invalidDirectories = [];

    /** @var array<string, true> */
    private array $enabledIdentifiers;

    /** @var array<string, string> */
    private array $legacyProvidersBySlug;

    /** @var array<string, string> */
    private array $legacyProvidersByDirectory;

    /** @var array<string, string> */
    private array $legacySlugsByDirectory;

    /**
     * @param list<string> $enabledIdentifiers
     * @param array<string, class-string<\Illuminate\Support\ServiceProvider>> $legacyProviders
     */
    public function __construct(
        private readonly string $extensionsPath,
        array $enabledIdentifiers = [],
        array $legacyProviders = [],
    ) {
        $this->enabledIdentifiers = [];
        foreach ($enabledIdentifiers as $identifier) {
            $normalised = self::normaliseIdentifier($identifier);
            if ($normalised !== '') {
                $this->enabledIdentifiers[$normalised] = true;
            }
        }

        $this->legacyProvidersBySlug = [];
        $this->legacyProvidersByDirectory = [];
        $this->legacySlugsByDirectory = [];
        foreach ($legacyProviders as $slug => $provider) {
            if (! is_string($slug) || ! is_string($provider) || trim($provider) === '') {
                continue;
            }

            $normalisedSlug = self::normaliseIdentifier($slug);
            $this->legacyProvidersBySlug[$normalisedSlug] = $provider;

            $directory = self::directoryFromProvider($provider);
            if ($directory !== null) {
                $this->legacyProvidersByDirectory[$directory] = $provider;
                $this->legacySlugsByDirectory[$directory] = $normalisedSlug;
            }
        }
    }

    /** @return list<ExtensionManifest> */
    public function manifests(): array
    {
        $this->scan();

        return $this->manifests ?? [];
    }

    /** @return list<string> */
    public function issues(): array
    {
        $this->scan();

        return $this->issues;
    }

    public function isValid(ExtensionManifest $manifest): bool
    {
        $this->scan();

        return ! isset($this->invalidDirectories[$manifest->directory]);
    }

    private function scan(): void
    {
        if ($this->manifests !== null) {
            return;
        }

        $this->manifests = [];
        if (! is_dir($this->extensionsPath)) {
            $this->issues[] = "Extension directory [{$this->extensionsPath}] does not exist.";
            return;
        }

        $paths = glob(rtrim($this->extensionsPath, DIRECTORY_SEPARATOR) . '/*/extension.json') ?: [];
        sort($paths, SORT_STRING);

        foreach ($paths as $manifestPath) {
            $directory = basename(dirname($manifestPath));
            $manifest = $this->parseManifest($manifestPath, $directory);
            if ($manifest !== null) {
                $this->manifests[] = $manifest;
            }
        }

        $this->detectDuplicates();
    }

    private function parseManifest(string $path, string $directory): ?ExtensionManifest
    {
        $contents = file_get_contents($path);
        if (! is_string($contents)) {
            $this->invalidate($directory, "{$directory}: extension manifest could not be read.");
            return null;
        }

        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->invalidate($directory, "{$directory}: invalid JSON in extension manifest ({$exception->getMessage()}).");
            return null;
        }

        if (! is_array($data)) {
            $this->invalidate($directory, "{$directory}: extension manifest must be a JSON object.");
            return null;
        }

        $name = trim((string) ($data['name'] ?? ''));
        $version = trim((string) ($data['version'] ?? ''));
        $type = trim((string) ($data['type'] ?? ''));
        if ($name === '' || $version === '' || $type !== 'extension') {
            $this->invalidate($directory, "{$directory}: manifest requires non-empty name/version and type=extension.");
            return null;
        }

        $slug = self::normaliseIdentifier((string) ($data['slug'] ?? $data['key'] ?? ''));
        if ($slug === '') {
            $slug = $this->legacySlugsByDirectory[$directory] ?? self::normaliseIdentifier($directory);
        }

        $provider = $this->manifestProvider($data);
        if ($provider === null) {
            $provider = $this->legacyProvidersBySlug[$slug]
                ?? $this->legacyProvidersByDirectory[$directory]
                ?? null;
        }

        $enabled = isset($this->enabledIdentifiers[$slug])
            || isset($this->enabledIdentifiers[self::normaliseIdentifier($directory)]);

        if ($enabled && $provider === null) {
            $this->invalidate($directory, "{$directory}: enabled extension has no provider declaration or legacy provider fallback.");
        }

        return new ExtensionManifest(
            directory: $directory,
            name: $name,
            version: $version,
            provider: $provider,
            enabled: $enabled,
        );
    }

    /** @param array<string, mixed> $data */
    private function manifestProvider(array $data): ?string
    {
        foreach (['provider', 'entry_provider'] as $field) {
            $provider = $data[$field] ?? null;
            if (! is_string($provider)) {
                continue;
            }

            $provider = trim($provider);
            if ($provider !== '' && str_contains($provider, '\\')) {
                return ltrim($provider, '\\');
            }
        }

        return null;
    }

    private function detectDuplicates(): void
    {
        $names = [];
        $providers = [];
        foreach ($this->manifests ?? [] as $manifest) {
            $names[strtolower(trim($manifest->name))][] = $manifest->directory;
            if ($manifest->provider !== null) {
                $providers[strtolower(ltrim($manifest->provider, '\\'))][] = $manifest->directory;
            }
        }

        foreach ($names as $directories) {
            if (count($directories) < 2) {
                continue;
            }
            sort($directories);
            foreach ($directories as $directory) {
                $this->invalidDirectories[$directory] = true;
            }
            $this->issues[] = 'Duplicate extension name across: ' . implode(', ', $directories) . '.';
        }

        foreach ($providers as $directories) {
            if (count($directories) < 2) {
                continue;
            }
            sort($directories);
            foreach ($directories as $directory) {
                $this->invalidDirectories[$directory] = true;
            }
            $this->issues[] = 'Duplicate provider class across: ' . implode(', ', $directories) . '.';
        }
    }

    private function invalidate(string $directory, string $issue): void
    {
        $this->invalidDirectories[$directory] = true;
        $this->issues[] = $issue;
    }

    private static function directoryFromProvider(string $provider): ?string
    {
        if (preg_match('/^App\\\\Extensions\\\\([^\\\\]+)\\\\/', ltrim($provider, '\\'), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private static function normaliseIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);
        $identifier = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $identifier) ?? $identifier;
        $identifier = str_replace(['_', ' '], '-', $identifier);
        $identifier = strtolower($identifier);
        $identifier = preg_replace('/[^a-z0-9-]+/', '-', $identifier) ?? $identifier;
        return trim((string) preg_replace('/-+/', '-', $identifier), '-');
    }
}
