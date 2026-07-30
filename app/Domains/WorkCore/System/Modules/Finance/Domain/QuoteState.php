<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Domain;

final class QuoteState
{
    /** @var array<string,list<string>> */
    private const TRANSITIONS = [
        'draft' => ['issued', 'cancelled'],
        'issued' => ['accepted', 'rejected', 'expired', 'cancelled'],
        'accepted' => ['converted', 'cancelled'],
        'rejected' => [],
        'expired' => [],
        'converted' => [],
        'cancelled' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }
}
