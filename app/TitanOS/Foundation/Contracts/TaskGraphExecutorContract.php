<?php

namespace App\TitanOS\Foundation\Contracts;

interface TaskGraphExecutorContract
{
    /**
     * Execute a task graph with checkpoints and resume capability.
     *
     * @param string $planPath Path to task graph YAML file
     * @param array $context Execution context
     * @param string|null $resumeFromCheckpoint Resume from checkpoint
     * @return array Execution results with final state
     *
     * @throws \App\TitanOS\Foundation\Exceptions\TaskExecutionException
     */
    public function execute(string $planPath, array $context = [], ?string $resumeFromCheckpoint = null): array;

    /**
     * Validate task graph structure and dependencies.
     *
     * @param array $graph Task graph data
     * @return bool
     *
     * @throws \App\TitanOS\Foundation\Exceptions\InvalidTaskGraphException
     */
    public function validateGraph(array $graph): bool;

    /**
     * Get execution status and checkpoint info.
     *
     * @param string $executionId
     * @return array Status with checkpoint details
     */
    public function getStatus(string $executionId): array;

    /**
     * Pause execution at current checkpoint.
     *
     * @param string $executionId
     * @return void
     */
    public function pause(string $executionId): void;

    /**
     * Resume from checkpoint.
     *
     * @param string $executionId
     * @param string $checkpointId
     * @return array Execution results
     */
    public function resume(string $executionId, string $checkpointId): array;
}
