<?php

declare(strict_types=1);

namespace App\Titan\Extensions;

final readonly class ExtensionDefinition
{
    /**
     * @param list<string> $provides
     * @param list<string> $permissions
     * @param array<string,mixed> $requires
     * @param array<string,mixed> $manifest
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public string $type,
        public string $provider,
        public bool $enabledByDefault,
        public array $provides,
        public array $permissions,
        public array $requires,
        public string $path,
        public array $manifest,
    ) {}
}
