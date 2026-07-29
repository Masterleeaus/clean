<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Domain;

final class VoiceSessionState
{
    private const TRANSITIONS = [
        'created' => ['connecting', 'cancelled', 'failed'],
        'connecting' => ['active', 'cancelled', 'failed'],
        'active' => ['completed', 'cancelled', 'failed'],
        'completed' => [],
        'cancelled' => [],
        'failed' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function terminal(string $state): bool
    {
        return in_array($state, ['completed', 'cancelled', 'failed'], true);
    }
}
