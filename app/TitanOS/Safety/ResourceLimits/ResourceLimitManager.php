<?php

namespace App\TitanOS\Safety\ResourceLimits;

use App\TitanOS\Safety\Contracts\ResourceLimitContract;
use Illuminate\Support\Facades\Cache;

class ResourceLimitManager implements ResourceLimitContract
{
    private const LIMIT_PREFIX = 'titan:resource:limit:';
    private const USAGE_PREFIX = 'titan:resource:usage:';
    private array $limits = [];
    private array $usage = [];

    private array $defaultLimits = [
        'cpu_percent' => 80,
        'memory_mb' => 512,
        'execution_time_seconds' => 300,
        'file_operations' => 1000,
    ];

    public function setAgentLimits(string $agentId, array $limits): void
    {
        $merged = array_merge($this->defaultLimits, $limits);

        $this->limits[$agentId] = [
            'agent_id' => $agentId,
            'limits' => $merged,
            'set_at' => now()->toIso8601String(),
        ];

        Cache::put(self::LIMIT_PREFIX . $agentId, $this->limits[$agentId], 86400);
    }

    public function getAgentLimits(string $agentId): array
    {
        return $this->limits[$agentId] ?? Cache::get(self::LIMIT_PREFIX . $agentId, []);
    }

    public function checkViolations(string $agentId): array
    {
        $limits = $this->getAgentLimits($agentId);
        if (empty($limits)) {
            return [];
        }

        $currentUsage = $this->usage[$agentId] ?? Cache::get(self::USAGE_PREFIX . $agentId, []);
        $violations = [];

        foreach ($limits['limits'] as $resource => $limit) {
            $used = $currentUsage[$resource] ?? 0;
            if ($used > $limit) {
                $violations[] = [
                    'resource' => $resource,
                    'limit' => $limit,
                    'usage' => $used,
                    'exceeded_by' => $used - $limit,
                ];
            }
        }

        return $violations;
    }

    public function recordUsage(string $agentId, array $usage): void
    {
        if (!isset($this->usage[$agentId])) {
            $this->usage[$agentId] = array_merge($this->defaultLimits, ['timestamp' => now()->toIso8601String()]);
        }

        foreach ($usage as $resource => $value) {
            $this->usage[$agentId][$resource] = ($this->usage[$agentId][$resource] ?? 0) + $value;
        }

        $this->usage[$agentId]['last_updated'] = now()->toIso8601String();
        Cache::put(self::USAGE_PREFIX . $agentId, $this->usage[$agentId], 86400);
    }

    public function getUsageStats(string $agentId): array
    {
        $usage = $this->usage[$agentId] ?? Cache::get(self::USAGE_PREFIX . $agentId, []);

        return [
            'agent_id' => $agentId,
            'current_usage' => $usage,
            'limits' => $this->getAgentLimits($agentId)['limits'] ?? [],
            'utilization' => $this->calculateUtilization($agentId, $usage),
            'last_updated' => $usage['last_updated'] ?? null,
        ];
    }

    public function resetUsage(string $agentId): void
    {
        $this->usage[$agentId] = array_merge(
            $this->defaultLimits,
            ['timestamp' => now()->toIso8601String()]
        );
        Cache::forget(self::USAGE_PREFIX . $agentId);
    }

    private function calculateUtilization(string $agentId, array $usage): array
    {
        $limits = $this->getAgentLimits($agentId);
        $utilization = [];

        if (empty($limits)) {
            return $utilization;
        }

        foreach ($limits['limits'] as $resource => $limit) {
            $used = $usage[$resource] ?? 0;
            $utilization[$resource] = $limit > 0 ? round(($used / $limit) * 100, 2) : 0;
        }

        return $utilization;
    }
}
