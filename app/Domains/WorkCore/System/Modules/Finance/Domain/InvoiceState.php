<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Domain;

final class InvoiceState
{
    /** @var array<string,list<string>> */
    private const TRANSITIONS = [
        'draft' => ['issued', 'void'],
        'issued' => ['viewed', 'overdue', 'partially_paid', 'paid', 'void', 'written_off'],
        'viewed' => ['overdue', 'partially_paid', 'paid', 'void', 'written_off'],
        'overdue' => ['partially_paid', 'paid', 'void', 'written_off'],
        'partially_paid' => ['paid', 'void', 'written_off'],
        'paid' => [],
        'void' => [],
        'written_off' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function isImmutable(string $state): bool
    {
        return $state !== 'draft';
    }
}
