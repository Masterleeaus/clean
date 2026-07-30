<?php

declare(strict_types=1);

namespace App\Support\Extensions;

final readonly class ExtensionManifest
{
    public function __construct(
        public string $directory,
        public string $name,
        public string $version,
        public ?string $provider,
        public bool $enabled,
    ) {
    }
}
