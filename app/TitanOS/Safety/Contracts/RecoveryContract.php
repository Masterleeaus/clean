<?php

namespace App\TitanOS\Safety\Contracts;

interface RecoveryContract
{
    /**
     * Create savepoint before risky operation.
     *
     * @param string $agentId
     * @param string $operationId Unique operation identifier
     * @param array $state State to save
     * @return string Savepoint ID
     */
    public function createSavepoint(string $agentId, string $operationId, array $state): string;

    /**
     * Rollback to previous savepoint.
     *
     * @param string $savepointId
     * @return array Restored state
     */
    public function rollback(string $savepointId): array;

    /**
     * Commit operation and release savepoint.
     *
     * @param string $savepointId
     * @return void
     */
    public function commit(string $savepointId): void;

    /**
     * Get list of available savepoints.
     *
     * @param string $agentId
     * @return array Savepoint history with timestamps
     */
    public function getSavepoints(string $agentId): array;

    /**
     * Handle agent failure.
     *
     * @param string $agentId
     * @param string $error Error message
     * @param array $context Failure context
     * @return array Recovery action to take
     */
    public function handleFailure(string $agentId, string $error, array $context): array;

    /**
     * Get recovery strategy for scenario.
     *
     * @param string $scenario Error scenario (timeout, deadlock, constraint_violation)
     * @return array Recovery steps to execute
     */
    public function getRecoveryStrategy(string $scenario): array;

    /**
     * Clear old savepoints.
     *
     * @param int $daysOld Delete savepoints older than this many days
     * @return int Number of savepoints deleted
     */
    public function clearOldSavepoints(int $daysOld = 30): int;
}
