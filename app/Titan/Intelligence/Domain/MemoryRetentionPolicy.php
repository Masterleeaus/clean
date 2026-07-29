<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Domain;

use DateTimeImmutable;

final class MemoryRetentionPolicy
{
    public static function mayPersist(bool $temporaryConversation, bool $explicitlyPromoted): bool
    {
        return ! $temporaryConversation || $explicitlyPromoted;
    }

    public static function expiresAt(DateTimeImmutable $createdAt, ?int $retentionDays): ?DateTimeImmutable
    {
        if ($retentionDays === null) return null;
        if ($retentionDays < 1) throw new \InvalidArgumentException('Retention days must be positive.');

        return $createdAt->modify('+' . $retentionDays . ' days');
    }

    public static function normaliseConfidence(float $confidence): float
    {
        return max(0.0, min(1.0, $confidence));
    }
}
