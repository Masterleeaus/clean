<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Domain;

final class ModelRoutingPolicy
{
    /**
     * @param array<int,array<string,mixed>> $models
     * @param list<string> $requiredCapabilities
     * @return array<int,array<string,mixed>>
     */
    public static function rank(array $models, array $requiredCapabilities = [], ?float $maxCost = null, ?int $maxLatencyMs = null): array
    {
        $eligible = [];
        foreach ($models as $model) {
            $capabilities = array_values(array_filter((array) ($model['capabilities'] ?? []), 'is_string'));
            if (array_diff($requiredCapabilities, $capabilities) !== []) continue;
            $cost = (float) ($model['cost'] ?? INF);
            $latency = (int) ($model['latency_ms'] ?? PHP_INT_MAX);
            if ($maxCost !== null && $cost > $maxCost) continue;
            if ($maxLatencyMs !== null && $latency > $maxLatencyMs) continue;
            $quality = max(0.0, min(1.0, (float) ($model['quality'] ?? 0.0)));
            $score = ($quality * 100.0) - ($cost * 4.0) - ($latency / 100.0);
            $model['routing_score'] = round($score, 4);
            $eligible[] = $model;
        }
        usort($eligible, static function (array $left, array $right): int {
            $score = ((float) $right['routing_score']) <=> ((float) $left['routing_score']);
            return $score !== 0 ? $score : strcmp((string) ($left['id'] ?? ''), (string) ($right['id'] ?? ''));
        });

        return $eligible;
    }
}
