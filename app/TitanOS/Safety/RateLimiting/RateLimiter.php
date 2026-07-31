<?php

namespace App\TitanOS\Safety\RateLimiting;

use App\TitanOS\Safety\Contracts\RateLimitContract;
use Illuminate\Support\Facades\Cache;

class RateLimiter implements RateLimitContract
{
    private const LIMIT_PREFIX = 'titan:ratelimit:limit:';
    private const USAGE_PREFIX = 'titan:ratelimit:usage:';
    private array $limits = [];
    private array $usage = [];

    public function setLimit(string $agentId, string $action, int $limit, int $windowSeconds): void
    {
        if (!isset($this->limits[$agentId])) {
            $this->limits[$agentId] = [];
        }

        $this->limits[$agentId][$action] = [
            'limit' => $limit,
            'window_seconds' => $windowSeconds,
            'set_at' => now()->toIso8601String(),
        ];

        Cache::put(self::LIMIT_PREFIX . $agentId, $this->limits[$agentId], 86400);
    }

    public function isAllowed(string $agentId, string $action): bool
    {
        $limits = $this->limits[$agentId] ?? Cache::get(self::LIMIT_PREFIX . $agentId, []);

        if (empty($limits) || !isset($limits[$action])) {
            return true;
        }

        $usage = $this->usage[$agentId][$action] ?? Cache::get(self::USAGE_PREFIX . "{$agentId}:{$action}", []);

        if (empty($usage)) {
            return true;
        }

        $now = now();
        $windowStart = $now->copy()->subSeconds($limits[$action]['window_seconds']);

        $recentRequests = array_filter(
            $usage['requests'] ?? [],
            fn($timestamp) => $timestamp > $windowStart->toIso8601String()
        );

        return count($recentRequests) < $limits[$action]['limit'];
    }

    public function recordAction(string $agentId, string $action, bool $allowed = true): void
    {
        if (!isset($this->usage[$agentId])) {
            $this->usage[$agentId] = [];
        }

        if (!isset($this->usage[$agentId][$action])) {
            $this->usage[$agentId][$action] = ['requests' => [], 'blocked' => 0];
        }

        if ($allowed) {
            $this->usage[$agentId][$action]['requests'][] = now()->toIso8601String();
        } else {
            $this->usage[$agentId][$action]['blocked']++;
        }

        Cache::put(
            self::USAGE_PREFIX . "{$agentId}:{$action}",
            $this->usage[$agentId][$action],
            86400
        );
    }

    public function getStatus(string $agentId, string $action): array
    {
        $limits = $this->limits[$agentId] ?? Cache::get(self::LIMIT_PREFIX . $agentId, []);
        $usage = $this->usage[$agentId][$action] ?? Cache::get(self::USAGE_PREFIX . "{$agentId}:{$action}", []);

        if (empty($limits) || !isset($limits[$action])) {
            return ['allowed' => true, 'unlimited' => true];
        }

        $now = now();
        $windowStart = $now->copy()->subSeconds($limits[$action]['window_seconds']);

        $recentRequests = array_filter(
            $usage['requests'] ?? [],
            fn($timestamp) => $timestamp > $windowStart->toIso8601String()
        );

        $currentUsage = count($recentRequests);
        $limit = $limits[$action]['limit'];

        return [
            'action' => $action,
            'limit' => $limit,
            'window_seconds' => $limits[$action]['window_seconds'],
            'current_usage' => $currentUsage,
            'remaining' => max(0, $limit - $currentUsage),
            'blocked_count' => $usage['blocked'] ?? 0,
            'reset_at' => count($recentRequests) > 0 
                ? reset($recentRequests) 
                : $now->toIso8601String(),
        ];
    }

    public function getViolations(string $agentId): array
    {
        $violations = [];

        foreach ($this->usage[$agentId] ?? [] as $action => $data) {
            if (($data['blocked'] ?? 0) > 0) {
                $violations[] = [
                    'action' => $action,
                    'times_blocked' => $data['blocked'],
                ];
            }
        }

        return $violations;
    }

    public function reset(string $agentId, ?string $action = null): void
    {
        if ($action) {
            if (isset($this->usage[$agentId][$action])) {
                unset($this->usage[$agentId][$action]);
                Cache::forget(self::USAGE_PREFIX . "{$agentId}:{$action}");
            }
        } else {
            unset($this->usage[$agentId]);
            Cache::forget(self::USAGE_PREFIX . $agentId);
        }
    }
}
