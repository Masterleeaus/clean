<?php

declare(strict_types=1);

namespace App\Titan\Vault;

interface Vault
{
    /** @param array<string,mixed> $metadata */
    public function put(
        int $companyId,
        string $provider,
        string $key,
        string $value,
        ?int $userId = null,
        array $metadata = [],
    ): string;

    public function resolve(int $companyId, string $reference, ?int $userId = null): string;

    public function forget(int $companyId, string $reference, ?int $userId = null): bool;
}
