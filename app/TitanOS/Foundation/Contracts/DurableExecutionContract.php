<?php

namespace App\TitanOS\Foundation\Contracts;

use DateTime;

interface DurableExecutionContract
{
    /**
     * Create checkpoint at current execution state.
     *
     * @param string $executionId
     * @param array $state Current state
     * @param array $evidence Task outputs and verification
     * @return string Checkpoint ID
     */
    public function createCheckpoint(string $executionId, array $state, array $evidence = []): string;

    /**
     * Restore execution state from checkpoint.
     *
     * @param string $checkpointId
     * @return array Restored state
     *
     * @throws \App\TitanOS\Foundation\Exceptions\CheckpointNotFoundException
     */
    public function restoreFromCheckpoint(string $checkpointId): array;

    /**
     * Verify checkpoint integrity and completeness.
     *
     * @param string $checkpointId
     * @return bool
     */
    public function verifyCheckpoint(string $checkpointId): bool;

    /**
     * Rollback to previous checkpoint.
     *
     * @param string $executionId
     * @param string|null $targetCheckpoint Target checkpoint or previous
     * @return void
     *
     * @throws \App\TitanOS\Foundation\Exceptions\RollbackException
     */
    public function rollback(string $executionId, ?string $targetCheckpoint = null): void;

    /**
     * List checkpoints for execution.
     *
     * @param string $executionId
     * @return array List of checkpoints with metadata
     */
    public function listCheckpoints(string $executionId): array;

    /**
     * Mark task as completed with evidence.
     *
     * @param string $executionId
     * @param string $taskId
     * @param array $evidence Proof of completion
     * @return void
     */
    public function markTaskComplete(string $executionId, string $taskId, array $evidence): void;

    /**
     * Get execution trace for debugging.
     *
     * @param string $executionId
     * @return array Detailed execution trace
     */
    public function getExecutionTrace(string $executionId): array;
}
