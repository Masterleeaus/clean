<?php

namespace App\TitanOS\Safety\Recovery;

use App\TitanOS\Safety\Contracts\RecoveryContract;
use App\TitanOS\Safety\Exceptions\RecoveryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class RecoveryManager implements RecoveryContract
{
    private const SAVEPOINT_PREFIX = 'titan:savepoint:';
    private array $savepoints = [];
    private array $strategies = [];

    public function __construct()
    {
        $this->initializeStrategies();
    }

    public function createSavepoint(string $agentId, string $operationId, array $state): string
    {
        $savepointId = Str::uuid()->toString();

        $savepoint = [
            'id' => $savepointId,
            'agent_id' => $agentId,
            'operation_id' => $operationId,
            'state' => $state,
            'created_at' => now()->toIso8601String(),
            'status' => 'active',
        ];

        if (!isset($this->savepoints[$agentId])) {
            $this->savepoints[$agentId] = [];
        }

        $this->savepoints[$agentId][$savepointId] = $savepoint;
        Cache::put(self::SAVEPOINT_PREFIX . $savepointId, $savepoint, 86400);

        return $savepointId;
    }

    public function rollback(string $savepointId): array
    {
        $savepoint = null;
        $agentId = null;

        foreach ($this->savepoints as $agent => $points) {
            if (isset($points[$savepointId])) {
                $savepoint = $points[$savepointId];
                $agentId = $agent;
                break;
            }
        }

        if (!$savepoint) {
            $savepoint = Cache::get(self::SAVEPOINT_PREFIX . $savepointId);
        }

        if (!$savepoint) {
            throw new RecoveryException("Savepoint not found: {$savepointId}");
        }

        if ($savepoint['status'] !== 'active') {
            throw new RecoveryException("Cannot rollback non-active savepoint: {$savepointId}");
        }

        $savedState = $savepoint['state'];
        $savepoint['status'] = 'rolled_back';
        $savepoint['rolled_back_at'] = now()->toIso8601String();

        return $savedState;
    }

    public function commit(string $savepointId): void
    {
        foreach ($this->savepoints as $agentId => $points) {
            if (isset($points[$savepointId])) {
                $this->savepoints[$agentId][$savepointId]['status'] = 'committed';
                $this->savepoints[$agentId][$savepointId]['committed_at'] = now()->toIso8601String();
                return;
            }
        }

        $savepoint = Cache::get(self::SAVEPOINT_PREFIX . $savepointId);
        if ($savepoint) {
            $savepoint['status'] = 'committed';
            $savepoint['committed_at'] = now()->toIso8601String();
            Cache::put(self::SAVEPOINT_PREFIX . $savepointId, $savepoint, 86400);
        }
    }

    public function getSavepoints(string $agentId): array
    {
        return $this->savepoints[$agentId] ?? [];
    }

    public function handleFailure(string $agentId, string $error, array $context): array
    {
        $scenario = $this->categorizeError($error);
        $strategy = $this->getRecoveryStrategy($scenario);

        return [
            'agent_id' => $agentId,
            'error' => $error,
            'scenario' => $scenario,
            'recommended_action' => $strategy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function getRecoveryStrategy(string $scenario): array
    {
        return $this->strategies[$scenario] ?? $this->strategies['unknown'] ?? [];
    }

    public function clearOldSavepoints(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        $deleted = 0;

        foreach ($this->savepoints as $agentId => $points) {
            $remaining = array_filter($points, function ($savepoint) use ($cutoffDate) {
                if ($savepoint['status'] === 'active') {
                    return true;
                }

                $createdAt = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $savepoint['created_at']);
                return $createdAt && $createdAt > $cutoffDate;
            });

            $deleted += count($points) - count($remaining);
            $this->savepoints[$agentId] = $remaining;
        }

        return $deleted;
    }

    private function initializeStrategies(): void
    {
        $this->strategies = [
            'timeout' => [
                'priority' => 'high',
                'actions' => ['interrupt', 'rollback', 'reschedule'],
                'duration' => 300,
                'description' => 'Agent operation timed out',
            ],
            'deadlock' => [
                'priority' => 'critical',
                'actions' => ['release_locks', 'rollback', 'retry_with_backoff'],
                'backoff' => 2000,
                'max_retries' => 3,
                'description' => 'Deadlock detected in resource locks',
            ],
            'constraint_violation' => [
                'priority' => 'high',
                'actions' => ['rollback', 'validate_state', 'notify_operator'],
                'description' => 'Business logic constraint violated',
            ],
            'resource_exhaustion' => [
                'priority' => 'high',
                'actions' => ['cleanup', 'throttle', 'queue_task'],
                'description' => 'Resource limits exceeded',
            ],
            'unknown' => [
                'priority' => 'medium',
                'actions' => ['log_error', 'notify_operator', 'rollback'],
                'description' => 'Unknown error occurred',
            ],
        ];
    }

    private function categorizeError(string $error): string
    {
        if (str_contains($error, 'timeout') || str_contains($error, 'timed out')) {
            return 'timeout';
        }
        if (str_contains($error, 'deadlock')) {
            return 'deadlock';
        }
        if (str_contains($error, 'constraint') || str_contains($error, 'validation')) {
            return 'constraint_violation';
        }
        if (str_contains($error, 'resource') || str_contains($error, 'memory') || str_contains($error, 'limit')) {
            return 'resource_exhaustion';
        }

        return 'unknown';
    }
}
