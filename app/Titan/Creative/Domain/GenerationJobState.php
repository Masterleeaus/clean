<?php

declare(strict_types=1);

namespace App\Titan\Creative\Domain;

final class GenerationJobState
{
    private const TRANSITIONS = [
        'queued' => ['processing', 'cancelled'],
        'processing' => ['succeeded', 'failed', 'cancelled'],
        'succeeded' => [],
        'failed' => [],
        'cancelled' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array(strtolower(trim($to)), self::TRANSITIONS[strtolower(trim($from))] ?? [], true);
    }

    public static function terminal(string $state): bool
    {
        return in_array(strtolower(trim($state)), ['succeeded', 'failed', 'cancelled'], true);
    }
}
