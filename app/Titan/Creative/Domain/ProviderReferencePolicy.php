<?php

declare(strict_types=1);

namespace App\Titan\Creative\Domain;

final class ProviderReferencePolicy
{
    public static function requiresReference(string $jobType): bool
    {
        return strtolower(trim($jobType)) !== 'manual';
    }

    public static function valid(string $jobType, ?string $reference): bool
    {
        return ! self::requiresReference($jobType) || trim((string) $reference) !== '';
    }
}
