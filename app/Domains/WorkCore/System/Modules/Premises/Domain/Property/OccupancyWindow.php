<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Property;

use DateTimeImmutable;
use InvalidArgumentException;

final class OccupancyWindow
{
    public static function overlaps(string $startA, ?string $endA, string $startB, ?string $endB): bool
    {
        $aStart = self::date($startA);
        $aEnd = $endA === null || trim($endA) === '' ? null : self::date($endA);
        $bStart = self::date($startB);
        $bEnd = $endB === null || trim($endB) === '' ? null : self::date($endB);
        self::assertWindow($aStart, $aEnd);
        self::assertWindow($bStart, $bEnd);
        return ($aEnd === null || $bStart < $aEnd) && ($bEnd === null || $aStart < $bEnd);
    }

    public static function contains(string $start, ?string $end, string $point): bool
    {
        $from = self::date($start);
        $until = $end === null || trim($end) === '' ? null : self::date($end);
        $at = self::date($point);
        self::assertWindow($from, $until);
        return $at >= $from && ($until === null || $at < $until);
    }

    private static function date(string $value): DateTimeImmutable
    {
        try { return new DateTimeImmutable($value); }
        catch (\Throwable) { throw new InvalidArgumentException('Invalid occupancy date.'); }
    }

    private static function assertWindow(DateTimeImmutable $start, ?DateTimeImmutable $end): void
    {
        if ($end !== null && $end <= $start) throw new InvalidArgumentException('Occupancy end must follow start.');
    }
}
