<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation;

final class HousekeepingState
{
    private const TRANSITIONS = [
        'dirty' => ['assigned', 'in_progress', 'cancelled'],
        'assigned' => ['in_progress', 'cancelled'],
        'in_progress' => ['inspected', 'ready', 'cancelled'],
        'inspected' => ['ready', 'in_progress', 'cancelled'],
        'ready' => ['dirty'],
        'cancelled' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array(strtolower(trim($to)), self::TRANSITIONS[strtolower(trim($from))] ?? [], true);
    }
}
