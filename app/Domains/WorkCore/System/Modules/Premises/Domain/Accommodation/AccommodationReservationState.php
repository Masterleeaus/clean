<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation;

final class AccommodationReservationState
{
    private const TRANSITIONS = [
        'draft' => ['tentative', 'confirmed', 'cancelled'],
        'tentative' => ['confirmed', 'cancelled', 'expired'],
        'confirmed' => ['checked_in', 'cancelled', 'no_show'],
        'checked_in' => ['checked_out'],
        'checked_out' => [],
        'cancelled' => [],
        'expired' => [],
        'no_show' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array(strtolower(trim($to)), self::TRANSITIONS[strtolower(trim($from))] ?? [], true);
    }

    public static function isCapacityConsuming(string $state): bool
    {
        return in_array(strtolower(trim($state)), ['tentative', 'confirmed', 'checked_in'], true);
    }
}
