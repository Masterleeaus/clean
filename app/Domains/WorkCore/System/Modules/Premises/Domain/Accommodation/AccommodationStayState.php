<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation;

final class AccommodationStayState
{
    private const TRANSITIONS = [
        'expected' => ['in_house', 'cancelled', 'no_show'],
        'in_house' => ['checked_out'],
        'checked_out' => [],
        'cancelled' => [],
        'no_show' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array(strtolower(trim($to)), self::TRANSITIONS[strtolower(trim($from))] ?? [], true);
    }
}
