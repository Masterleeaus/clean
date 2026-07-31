<?php

namespace App\TitanOS\Safety\Contracts;

interface RateLimitContract
{
    /**
     * Set rate limit for agent.
     *
     * @param string $agentId
     * @param string $action Action type to limit
     * @param int $limit Maximum number of requests
     * @param int $windowSeconds Time window in seconds
     * @return void
     */
    public function setLimit(string $agentId, string $action, int $limit, int $windowSeconds): void;

    /**
     * Check if action is allowed under rate limits.
     *
     * @param string $agentId
     * @param string $action Action to check
     * @return bool Whether action is allowed
     */
    public function isAllowed(string $agentId, string $action): bool;

    /**
     * Record action attempt.
     *
     * @param string $agentId
     * @param string $action
     * @param bool $allowed Whether action was permitted
     * @return void
     */
    public function recordAction(string $agentId, string $action, bool $allowed = true): void;

    /**
     * Get rate limit status.
     *
     * @param string $agentId
     * @param string $action
     * @return array Current usage and remaining quota
     */
    public function getStatus(string $agentId, string $action): array;

    /**
     * Get all rate limit violations.
     *
     * @param string $agentId
     * @return array List of actions that exceeded limits
     */
    public function getViolations(string $agentId): array;

    /**
     * Reset rate limits for agent.
     *
     * @param string $agentId
     * @param string|null $action Reset specific action or all if null
     * @return void
     */
    public function reset(string $agentId, ?string $action = null): void;
}
