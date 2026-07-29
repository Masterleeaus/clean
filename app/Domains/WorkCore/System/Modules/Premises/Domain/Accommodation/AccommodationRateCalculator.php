<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation;

use InvalidArgumentException;

final class AccommodationRateCalculator
{
    public static function currency(): string { return 'AUD'; }

    public static function total(float $baseAmount, string $basis, int $nights, int $spaces = 1, int $capacity = 1): float
    {
        if ($baseAmount < 0 || $nights < 1 || $spaces < 1 || $capacity < 1) throw new InvalidArgumentException('Rate inputs must be positive.');
        $units = match (strtolower(trim($basis))) {
            'night' => $nights * $spaces,
            'week' => (int) ceil($nights / 7) * $spaces,
            'month' => (int) ceil($nights / 30) * $spaces,
            'stay' => $spaces,
            'person_night', 'bed_night' => $nights * $capacity,
            default => throw new InvalidArgumentException('Unsupported accommodation pricing basis.'),
        };
        return round($baseAmount * $units, 4);
    }
}
