<?php

declare(strict_types=1);

namespace App\Titan\Creative\Domain;

final class PublicationState
{
    private const TRANSITIONS = [
        'draft' => ['scheduled', 'publishing', 'cancelled'],
        'scheduled' => ['publishing', 'cancelled'],
        'publishing' => ['published', 'failed'],
        'published' => ['retracted'],
        'failed' => [],
        'cancelled' => [],
        'retracted' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array(strtolower(trim($to)), self::TRANSITIONS[strtolower(trim($from))] ?? [], true);
    }

    public static function terminal(string $state): bool
    {
        return in_array(strtolower(trim($state)), ['failed', 'cancelled', 'retracted'], true);
    }
}
