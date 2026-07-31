<?php

namespace App\TitanOS\Safety\Contracts;

interface ResourceLimitContract
{
    /**
     * Set resource limits for an agent.
     *
     * @param string $agentId Agent identifier
     * @param array $limits CPU, memory, time limits
     * @return void
     */
    public function setAgentLimits(string $agentId, array $limits): void;

    /**
     * Get resource limits for an agent.
     *
     * @param string $agentId
     * @return array Agent resource limits
     */
    public function getAgentLimits(string $agentId): array;

    /**
     * Check if agent has exceeded limits.
     *
     * @param string $agentId
     * @return array Violations with limit exceeded details
     */
    public function checkViolations(string $agentId): array;

    /**
     * Record resource usage.
     *
     * @param string $agentId
     * @param array $usage Current CPU, memory, time usage
     * @return void
     */
    public function recordUsage(string $agentId, array $usage): void;

    /**
     * Get resource usage statistics.
     *
     * @param string $agentId
     * @return array Usage stats with peak and average
     */
    public function getUsageStats(string $agentId): array;

    /**
     * Reset usage counters for agent.
     *
     * @param string $agentId
     * @return void
     */
    public function resetUsage(string $agentId): void;
}
