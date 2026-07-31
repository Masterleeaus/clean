<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Domain;

final class AgentRunState
{
    private const TRANSITIONS = [
        'queued' => ['running', 'cancelled'],
        'running' => ['waiting', 'succeeded', 'failed', 'cancelled'],
        'waiting' => ['running', 'failed', 'cancelled'],
        'succeeded' => [],
        'failed' => [],
        'cancelled' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function terminal(string $state): bool
    {
        return in_array($state, ['succeeded', 'failed', 'cancelled'], true);
    }
}
