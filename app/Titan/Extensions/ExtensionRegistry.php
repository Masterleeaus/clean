<?php

declare(strict_types=1);

namespace App\Titan\Extensions;

use InvalidArgumentException;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ExtensionRegistry
{
    /** @var list<string> */
    private const ALLOWED_TYPES = [
        'ai-capability',
        'integration',
        'channel',
        'interface',
        'provider',
        'theme',
        'developer-tool',
    ];

    /** @var array<string,ExtensionDefinition> */
    private array $definitions = [];

    public function __construct(private readonly string $extensionRoot) {}

    /** @return array<string,ExtensionDefinition> */
    public function discover(): array
    {
        $this->definitions = [];
        if (! is_dir($this->extensionRoot)) {
            return [];
        }

        $manifests = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->extensionRoot, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'extension.json') {
                $manifests[] = $file->getPathname();
            }
        }
        sort($manifests);

        $capabilityOwners = [];
        foreach ($manifests as $manifestPath) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
            $normalized = $this->validateManifest($manifest);
            $id = $normalized['id'];
            if (isset($this->definitions[$id])) {
                throw new LogicException("Duplicate extension id: {$id}");
            }

            foreach ($normalized['provides'] as $capability) {
                if (isset($capabilityOwners[$capability])) {
                    throw new LogicException("Cannot register duplicate capability {$capability}; owned by {$capabilityOwners[$capability]} and {$id}.");
                }
                $capabilityOwners[$capability] = $id;
            }

            $this->definitions[$id] = new ExtensionDefinition(
                id: $id,
                name: $normalized['name'],
                version: $normalized['version'],
                type: $normalized['type'],
                provider: $normalized['provider'],
                enabledByDefault: $normalized['enabled_by_default'],
                provides: $normalized['provides'],
                permissions: $normalized['permissions'],
                requires: $normalized['requires'],
                path: dirname($manifestPath),
                manifest: $manifest,
            );
        }

        return $this->definitions;
    }

    /** @param array<string,mixed> $manifest @return array<string,mixed> */
    public function validateManifest(array $manifest): array
    {
        if (($manifest['type'] ?? null) === 'domain') {
            throw new InvalidArgumentException("The prohibited 'domain' extension type duplicates WorkCore authority.");
        }

        foreach (['id', 'name', 'version', 'type', 'provider'] as $required) {
            if (! isset($manifest[$required]) || ! is_string($manifest[$required]) || trim($manifest[$required]) === '') {
                throw new InvalidArgumentException("Extension manifest field {$required} is required.");
            }
        }
        if (($manifest['schema_version'] ?? null) !== '2.0') {
            throw new InvalidArgumentException('Extension schema_version must equal 2.0.');
        }
        if (! in_array($manifest['type'], self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException("Unsupported extension type: {$manifest['type']}");
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $manifest['id'])) {
            throw new InvalidArgumentException('Extension id must be a lowercase kebab-case identifier.');
        }

        $provides = array_values(array_unique(array_filter((array) ($manifest['provides'] ?? []), 'is_string')));
        $permissions = array_values(array_unique(array_filter((array) ($manifest['permissions'] ?? []), 'is_string')));

        return [
            'id' => trim($manifest['id']),
            'name' => trim($manifest['name']),
            'version' => trim($manifest['version']),
            'type' => trim($manifest['type']),
            'provider' => trim($manifest['provider']),
            'enabled_by_default' => (bool) ($manifest['enabled_by_default'] ?? false),
            'provides' => $provides,
            'permissions' => $permissions,
            'requires' => is_array($manifest['requires'] ?? null) ? $manifest['requires'] : [],
        ];
    }
}
