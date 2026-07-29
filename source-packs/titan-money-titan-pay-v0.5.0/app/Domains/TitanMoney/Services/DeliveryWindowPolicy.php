<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Models\Company;
use Carbon\CarbonImmutable;

final class DeliveryWindowPolicy
{
    public function nextAllowedAt(Company $company, string $channel): CarbonImmutable
    {
        $now = CarbonImmutable::now((string) ($company->timezone ?: 'Australia/Sydney'));
        if (! in_array($channel, ['sms','voice','whatsapp'], true)) {
            return $now;
        }

        $start = (string) config('titan_channels.quiet_hours.end', '08:00');
        $end = (string) config('titan_channels.quiet_hours.start', '20:00');
        [$startHour, $startMinute] = array_map('intval', explode(':', $start));
        [$endHour, $endMinute] = array_map('intval', explode(':', $end));
        $windowStart = $now->setTime($startHour, $startMinute);
        $windowEnd = $now->setTime($endHour, $endMinute);

        if ($now->lt($windowStart)) {
            return $windowStart;
        }
        if ($now->gte($windowEnd)) {
            return $windowStart->addDay();
        }

        return $now;
    }
}
