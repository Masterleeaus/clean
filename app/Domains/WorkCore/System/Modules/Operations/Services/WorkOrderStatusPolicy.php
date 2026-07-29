<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Operations\Services;

use InvalidArgumentException;

final class WorkOrderStatusPolicy
{
    /** @var array<string,array<int,string>> */
    private const TRANSITIONS = [
        'draft' => ['ready', 'cancelled'],
        'ready' => ['in_progress', 'cancelled'],
        'in_progress' => ['paused', 'completed', 'cancelled'],
        'paused' => ['in_progress', 'cancelled'],
        'completed' => ['in_progress'],
        'cancelled' => ['draft'],
    ];

    public function assertTransition(string $fromStatus, string $toStatus, ?string $reason, int $incompleteRequiredTasks): void
    {
        if ($fromStatus === $toStatus) {
            return;
        }
        if (! in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true)) {
            throw new InvalidArgumentException("Work order cannot move from {$fromStatus} to {$toStatus}.");
        }
        if (($toStatus === 'cancelled' || $fromStatus === 'completed' || $fromStatus === 'cancelled') && trim((string) $reason) === '') {
            throw new InvalidArgumentException('A reason is required when cancelling or reopening a closed work order.');
        }
        if ($toStatus === 'completed' && $incompleteRequiredTasks > 0) {
            throw new InvalidArgumentException('All required work-order tasks must be completed or skipped before completion.');
        }
    }
}
