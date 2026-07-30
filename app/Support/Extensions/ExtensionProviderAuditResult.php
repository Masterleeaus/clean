<?php

declare(strict_types=1);

namespace App\Support\Extensions;

final readonly class ExtensionProviderAuditResult
{
    /** @param list<string> $diagnostics */
    public function __construct(
        public ExtensionManifest $manifest,
        public string $status,
        public array $diagnostics,
    ) {
    }
}
