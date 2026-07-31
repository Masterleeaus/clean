<?php

namespace App\TitanOS\Foundation\DurableExecution;

use App\TitanOS\Foundation\Contracts\DurableExecutionContract;
use App\TitanOS\Foundation\Exceptions\{CheckpointNotFoundException, TitanException};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DurableExecutor implements DurableExecutionContract
{
    private const CHECKPOINT_DISK = 'local';
    private const CHECKPOINT_PATH = '.titan/execution/checkpoints';
    private const TRACE_PATH = '.titan/execution/traces';

    public function createCheckpoint(string $executionId, array $state, array $evidence = []): string
    {
        $checkpointId = Str::uuid()->toString();

        $checkpoint = [
            'id' => $checkpointId,
            'execution_id' => $executionId,
            'created_at' => now()->toIso8601String(),
            'state' => $state,
            'evidence' => $evidence,
            'hash' => $this->hashCheckpoint($state, $evidence),
        ];

        $path = $this->getCheckpointPath($checkpointId);
        Storage::disk(self::CHECKPOINT_DISK)->put(
            $path,
            json_encode($checkpoint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->recordCheckpointInTrace($executionId, $checkpoint);

        return $checkpointId;
    }

    public function restoreFromCheckpoint(string $checkpointId): array
    {
        $path = $this->getCheckpointPath($checkpointId);

        if (!Storage::disk(self::CHECKPOINT_DISK)->exists($path)) {
            throw new CheckpointNotFoundException("Checkpoint not found: {$checkpointId}");
        }

        $content = Storage::disk(self::CHECKPOINT_DISK)->get($path);
        $checkpoint = json_decode($content, true);

        if (!$this->verifyCheckpointHash($checkpoint)) {
            throw new TitanException("Checkpoint integrity check failed: {$checkpointId}");
        }

        return $checkpoint['state'];
    }

    public function verifyCheckpoint(string $checkpointId): bool
    {
        try {
            $path = $this->getCheckpointPath($checkpointId);
            $content = Storage::disk(self::CHECKPOINT_DISK)->get($path);
            $checkpoint = json_decode($content, true);

            return $this->verifyCheckpointHash($checkpoint);
        } catch (\Exception) {
            return false;
        }
    }

    public function rollback(string $executionId, ?string $targetCheckpoint = null): void
    {
        $trace = $this->getExecutionTrace($executionId);

        if (empty($trace['checkpoints'])) {
            throw new TitanException("No checkpoints available for rollback: {$executionId}");
        }

        $checkpoints = $trace['checkpoints'];

        if ($targetCheckpoint) {
            $targetKey = array_search(
                $targetCheckpoint,
                array_column($checkpoints, 'id')
            );

            if ($targetKey === false) {
                throw new TitanException("Target checkpoint not found: {$targetCheckpoint}");
            }

            $targetCheckpoint = $checkpoints[$targetKey];
        } else {
            array_pop($checkpoints);
            if (empty($checkpoints)) {
                throw new TitanException("Cannot rollback - no previous checkpoint");
            }
            $targetCheckpoint = end($checkpoints);
        }

        $this->recordRollback($executionId, $targetCheckpoint['id']);
    }

    public function listCheckpoints(string $executionId): array
    {
        $trace = $this->getExecutionTrace($executionId);
        return $trace['checkpoints'] ?? [];
    }

    public function markTaskComplete(string $executionId, string $taskId, array $evidence): void
    {
        $trace = $this->getExecutionTrace($executionId);

        if (!isset($trace['tasks'])) {
            $trace['tasks'] = [];
        }

        $trace['tasks'][$taskId] = [
            'completed_at' => now()->toIso8601String(),
            'evidence' => $evidence,
        ];

        $this->updateTrace($executionId, $trace);
    }

    public function getExecutionTrace(string $executionId): array
    {
        $path = $this->getTracePath($executionId);

        if (!Storage::disk(self::CHECKPOINT_DISK)->exists($path)) {
            return [
                'execution_id' => $executionId,
                'started_at' => now()->toIso8601String(),
                'checkpoints' => [],
                'tasks' => [],
            ];
        }

        $content = Storage::disk(self::CHECKPOINT_DISK)->get($path);
        return json_decode($content, true);
    }

    private function getCheckpointPath(string $checkpointId): string
    {
        return self::CHECKPOINT_PATH . '/' . $checkpointId . '.json';
    }

    private function getTracePath(string $executionId): string
    {
        return self::TRACE_PATH . '/' . $executionId . '.json';
    }

    private function hashCheckpoint(array $state, array $evidence): string
    {
        $data = json_encode(['state' => $state, 'evidence' => $evidence]);
        return hash('sha256', $data);
    }

    private function verifyCheckpointHash(array $checkpoint): bool
    {
        $expectedHash = $this->hashCheckpoint($checkpoint['state'], $checkpoint['evidence']);
        return hash_equals($checkpoint['hash'] ?? '', $expectedHash);
    }

    private function recordCheckpointInTrace(string $executionId, array $checkpoint): void
    {
        $trace = $this->getExecutionTrace($executionId);

        if (!isset($trace['checkpoints'])) {
            $trace['checkpoints'] = [];
        }

        $trace['checkpoints'][] = [
            'id' => $checkpoint['id'],
            'created_at' => $checkpoint['created_at'],
        ];

        $this->updateTrace($executionId, $trace);
    }

    private function recordRollback(string $executionId, string $targetCheckpointId): void
    {
        $trace = $this->getExecutionTrace($executionId);

        if (!isset($trace['rollbacks'])) {
            $trace['rollbacks'] = [];
        }

        $trace['rollbacks'][] = [
            'rolled_back_at' => now()->toIso8601String(),
            'target_checkpoint' => $targetCheckpointId,
        ];

        $this->updateTrace($executionId, $trace);
    }

    private function updateTrace(string $executionId, array $trace): void
    {
        $path = $this->getTracePath($executionId);
        $trace['updated_at'] = now()->toIso8601String();

        Storage::disk(self::CHECKPOINT_DISK)->put(
            $path,
            json_encode($trace, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
