<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation;

use DateTimeImmutable;
use InvalidArgumentException;

final class AccommodationDateWindow
{
    public static function overlaps(string $arrivalA, string $departureA, string $arrivalB, string $departureB): bool
    {
        [$aStart, $aEnd] = self::window($arrivalA, $departureA);
        [$bStart, $bEnd] = self::window($arrivalB, $departureB);
        return $aStart < $bEnd && $bStart < $aEnd;
    }

    public static function nights(string $arrival, string $departure): int
    {
        [$start, $end] = self::window($arrival, $departure);
        $days = (int) $start->setTime(0, 0)->diff($end->setTime(0, 0))->format('%a');
        return max(1, $days);
    }

    /** @return array{0:DateTimeImmutable,1:DateTimeImmutable} */
    private static function window(string $arrival, string $departure): array
    {
        try { $start = new DateTimeImmutable($arrival); $end = new DateTimeImmutable($departure); }
        catch (\Throwable) { throw new InvalidArgumentException('Invalid accommodation date.'); }
        if ($end <= $start) throw new InvalidArgumentException('Departure must follow arrival.');
        return [$start, $end];
    }
}
