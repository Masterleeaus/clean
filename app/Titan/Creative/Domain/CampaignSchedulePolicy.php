<?php

declare(strict_types=1);

namespace App\Titan\Creative\Domain;

use DateTimeImmutable;
use Throwable;

final class CampaignSchedulePolicy
{
    public static function valid(?string $startDate, ?string $endDate): bool
    {
        try {
            $start = self::parse($startDate);
            $end = self::parse($endDate);
        } catch (Throwable) {
            return false;
        }

        return $start === null || $end === null || $end >= $start;
    }

    private static function parse(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', trim($value));
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new \InvalidArgumentException('Invalid date.');
        }

        return $date;
    }
}
