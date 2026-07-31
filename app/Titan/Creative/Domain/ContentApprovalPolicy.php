<?php

declare(strict_types=1);

namespace App\Titan\Creative\Domain;

final class ContentApprovalPolicy
{
    public static function mayPublish(bool $approvalRequired, string $status): bool
    {
        return ! $approvalRequired || strtolower(trim($status)) === 'approved';
    }

    public static function approvalStatus(bool $approvalRequired, ?string $status): string
    {
        if (! $approvalRequired) {
            return 'not_required';
        }

        $normalised = strtolower(trim((string) $status));
        return in_array($normalised, ['pending', 'approved', 'rejected'], true) ? $normalised : 'pending';
    }
}
