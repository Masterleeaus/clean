<?php

namespace App\TitanOS\Foundation\TaskGraphs;

use App\TitanOS\Foundation\Contracts\TaskGraphExecutorContract;
use App\TitanOS\Foundation\Exceptions\{InvalidTaskGraphException, TaskExecutionException};
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class TaskGraphExecutor implements TaskGraphExecutorContract
{
    private array $executions = [];
    private array $checkpoints = [];

    public function execute(string $planPath, array $context = [], ?string $resumeFromCheckpoint = null): array
    {
        if (!file_exists($planPath)) {
            throw new TaskExecutionException("Plan file not found: {$planPath}", ['path' => $planPath]);
        }

        try {
            $plan = Yaml::parseFile($planPath);
            $this->validateGraph($plan);

            $executionId = Str::uuid()->toString();
            $tasks = $plan['tasks'] ?? [];

            if ($resumeFromCheckpoint) {
                return $this->resumeExecution($executionId, $resumeFromCheckpoint, $context);
            }

            return $this->executeGraph($executionId, $tasks, $context, $plan);
        } catch (\Exception $e) {
            if ($e instanceof TaskExecutionException || $e instanceof InvalidTaskGraphException) {
                throw $e;
            }
            throw new TaskExecutionException("Plan execution failed: {$e->getMessage()}", previous: $e);
        }
    }

    public function validateGraph(array $graph): bool
    {
        if (empty($graph['tasks']) || !is_array($graph['tasks'])) {
            throw new InvalidTaskGraphException("Graph must have 'tasks' array");
        }

        foreach ($graph['tasks'] as $taskId => $task) {
            if (!isset($task['action'])) {
                throw new InvalidTaskGraphException("Task '{$taskId}' missing 'action'");
            }
        }

        $this->validateDependencies($graph['tasks']);
        return true;
    }

    private function validateDependencies(array $tasks): void
    {
        $taskIds = array_keys($tasks);

        foreach ($tasks as $taskId => $task) {
            if (isset($task['depends_on']) && is_array($task['depends_on'])) {
                foreach ($task['depends_on'] as $dep) {
                    if (!in_array($dep, $taskIds)) {
                        throw new InvalidTaskGraphException(
                            "Task '{$taskId}' depends on non-existent task '{$dep}'"
                        );
                    }
                }
            }
        }
    }

    private function executeGraph(string $executionId, array $tasks, array $context, array $plan): array
    {
        $this->executions[$executionId] = [
            'status' => 'running',
            'started_at' => now(),
            'tasks' => [],
            'context' => $context,
        ];

        $results = [];
        $completed = [];

        foreach ($tasks as $taskId => $task) {
            if (!$this->canExecute($task, $completed)) {
                continue;
            }

            try {
                $result = $this->executeTask($executionId, $taskId, $task, $context, $results);
                $results[$taskId] = $result;
                $completed[$taskId] = true;

                $this->createCheckpoint($executionId, $taskId, [
                    'task_id' => $taskId,
                    'result' => $result,
                    'completed_at' => now(),
                ]);
            } catch (\Exception $e) {
                $this->executions[$executionId]['status'] = 'failed';
                $this->executions[$executionId]['error'] = $e->getMessage();

                throw new TaskExecutionException("Task '{$taskId}' failed: {$e->getMessage()}", previous: $e);
            }
        }

        $this->executions[$executionId]['status'] = 'completed';
        $this->executions[$executionId]['completed_at'] = now();

        return [
            'execution_id' => $executionId,
            'status' => 'success',
            'tasks' => $results,
            'metadata' => $plan['metadata'] ?? [],
        ];
    }

    private function canExecute(array $task, array $completed): bool
    {
        if (empty($task['depends_on'])) {
            return true;
        }

        foreach ($task['depends_on'] as $dep) {
            if (!isset($completed[$dep])) {
                return false;
            }
        }

        return true;
    }

    private function executeTask(string $executionId, string $taskId, array $task, array $context, array $previousResults): mixed
    {
        $taskContext = array_merge($context, [
            'task_id' => $taskId,
            'previous_results' => $previousResults,
        ]);

        return [
            'task_id' => $taskId,
            'action' => $task['action'],
            'status' => 'completed',
            'executed_at' => now(),
            'context' => $taskContext,
        ];
    }

    private function createCheckpoint(string $executionId, string $taskId, array $evidence): string
    {
        $checkpointId = Str::uuid()->toString();

        $this->checkpoints[$checkpointId] = [
            'execution_id' => $executionId,
            'task_id' => $taskId,
            'created_at' => now(),
            'evidence' => $evidence,
        ];

        return $checkpointId;
    }

    private function resumeExecution(string $executionId, string $checkpointId, array $context): array
    {
        if (!isset($this->checkpoints[$checkpointId])) {
            throw new TaskExecutionException("Checkpoint not found: {$checkpointId}");
        }

        $checkpoint = $this->checkpoints[$checkpointId];

        return [
            'execution_id' => $executionId,
            'status' => 'resumed',
            'from_checkpoint' => $checkpointId,
            'resumed_at' => now(),
        ];
    }

    public function getStatus(string $executionId): array
    {
        if (!isset($this->executions[$executionId])) {
            throw new TaskExecutionException("Execution not found: {$executionId}");
        }

        $exec = $this->executions[$executionId];
        $checkpoints = array_filter($this->checkpoints, fn($cp) => $cp['execution_id'] === $executionId);

        return [
            'execution_id' => $executionId,
            'status' => $exec['status'],
            'checkpoints' => $checkpoints,
            'created_at' => $exec['started_at'],
            'completed_at' => $exec['completed_at'] ?? null,
        ];
    }

    public function pause(string $executionId): void
    {
        if (isset($this->executions[$executionId])) {
            $this->executions[$executionId]['status'] = 'paused';
            $this->executions[$executionId]['paused_at'] = now();
        }
    }

    public function resume(string $executionId, string $checkpointId): array
    {
        return $this->resumeExecution($executionId, $checkpointId, []);
    }
}
