<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI;

final class FollowUpStageResolver
{
    /**
     * @param array<int,array<string,mixed>> $stages
     * @param array<int,string> $completedStageKeys
     */
    public function resolve(int $daysOverdue, array $stages, array $completedStageKeys): ?string
    {
        if ($daysOverdue < 0) {
            return null;
        }

        $thresholdByKey = [];
        foreach ($stages as $stage) {
            $key = (string) ($stage['key'] ?? '');
            if ($key !== '') {
                $thresholdByKey[$key] = (int) ($stage['days_overdue'] ?? PHP_INT_MAX);
            }
        }

        $highestCompletedThreshold = -1;
        foreach ($completedStageKeys as $completedKey) {
            if (array_key_exists($completedKey, $thresholdByKey)) {
                $highestCompletedThreshold = max($highestCompletedThreshold, $thresholdByKey[$completedKey]);
            }
        }

        $eligible = array_values(array_filter($stages, static function (array $stage) use ($daysOverdue, $completedStageKeys, $highestCompletedThreshold): bool {
            $key = (string) ($stage['key'] ?? '');
            $threshold = (int) ($stage['days_overdue'] ?? PHP_INT_MAX);

            return $key !== ''
                && $threshold <= $daysOverdue
                && $threshold > $highestCompletedThreshold
                && ! in_array($key, $completedStageKeys, true);
        }));

        usort($eligible, static fn (array $a, array $b): int => ((int) ($b['days_overdue'] ?? 0)) <=> ((int) ($a['days_overdue'] ?? 0)));

        return isset($eligible[0]) ? (string) $eligible[0]['key'] : null;
    }

    /** @param array<int,array<string,mixed>> $stages */
    public function stage(string $key, array $stages): ?array
    {
        foreach ($stages as $stage) {
            if (($stage['key'] ?? null) === $key) {
                return $stage;
            }
        }

        return null;
    }
}
