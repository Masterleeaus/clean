<?php

namespace Tests\Feature\TitanOS\Foundation;

use App\TitanOS\Foundation\DurableExecution\DurableExecutor;
use App\TitanOS\Foundation\Exceptions\CheckpointNotFoundException;
use Tests\TestCase;

class DurableExecutorTest extends TestCase
{
    private DurableExecutor $executor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executor = new DurableExecutor();
    }

    /**
     * @test
     */
    public function it_creates_checkpoint_with_state()
    {
        $executionId = 'exec_test_' . uniqid();
        $state = ['step' => 1, 'data' => ['key' => 'value']];
        $evidence = ['test_passed' => true];

        $checkpointId = $this->executor->createCheckpoint($executionId, $state, $evidence);

        $this->assertNotEmpty($checkpointId);
        $this->assertTrue($this->executor->verifyCheckpoint($checkpointId));
    }

    /**
     * @test
     */
    public function it_restores_state_from_checkpoint()
    {
        $executionId = 'exec_test_' . uniqid();
        $originalState = ['step' => 2, 'data' => ['test' => 'data']];

        $checkpointId = $this->executor->createCheckpoint($executionId, $originalState);
        $restoredState = $this->executor->restoreFromCheckpoint($checkpointId);

        $this->assertEquals($originalState, $restoredState);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_missing_checkpoint()
    {
        $this->expectException(CheckpointNotFoundException::class);
        $this->executor->restoreFromCheckpoint('missing_checkpoint_id');
    }

    /**
     * @test
     */
    public function it_tracks_execution_trace()
    {
        $executionId = 'exec_trace_' . uniqid();
        $state = ['status' => 'running'];

        $checkpointId = $this->executor->createCheckpoint($executionId, $state);
        $trace = $this->executor->getExecutionTrace($executionId);

        $this->assertArrayHasKey('checkpoints', $trace);
        $this->assertNotEmpty($trace['checkpoints']);
        $this->assertEquals($checkpointId, $trace['checkpoints'][0]['id']);
    }

    /**
     * @test
     */
    public function it_lists_all_checkpoints_for_execution()
    {
        $executionId = 'exec_list_' . uniqid();

        $cp1 = $this->executor->createCheckpoint($executionId, ['step' => 1]);
        $cp2 = $this->executor->createCheckpoint($executionId, ['step' => 2]);
        $cp3 = $this->executor->createCheckpoint($executionId, ['step' => 3]);

        $checkpoints = $this->executor->listCheckpoints($executionId);

        $this->assertCount(3, $checkpoints);
    }

    /**
     * @test
     */
    public function it_marks_task_as_complete()
    {
        $executionId = 'exec_complete_' . uniqid();
        $evidence = ['tests_passed' => 10, 'time_ms' => 245];

        $this->executor->markTaskComplete($executionId, 'task_001', $evidence);

        $trace = $this->executor->getExecutionTrace($executionId);
        $this->assertArrayHasKey('tasks', $trace);
        $this->assertArrayHasKey('task_001', $trace['tasks']);
    }
}
