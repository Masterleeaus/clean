<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Domain\Property;

final class AgreementState
{
    private const TRANSITIONS = [
        'draft' => ['pending', 'active', 'cancelled'],
        'pending' => ['active', 'cancelled'],
        'active' => ['suspended', 'ended', 'cancelled'],
        'suspended' => ['active', 'ended', 'cancelled'],
        'ended' => [],
        'cancelled' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function isCapacityConsuming(string $state): bool
    {
        return in_array(strtolower(trim($state)), ['active', 'suspended'], true);
    }
}
