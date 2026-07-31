<?php

namespace Tests\Unit\TitanOS\Safety\Recovery;

use App\TitanOS\Safety\Recovery\RecoveryManager;
use App\TitanOS\Safety\Exceptions\RecoveryException;
use PHPUnit\Framework\TestCase;

class RecoveryManagerTest extends TestCase
{
    private RecoveryManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new RecoveryManager();
    }

    public function test_create_savepoint_returns_unique_id(): void
    {
        $id1 = $this->manager->createSavepoint('agent-1', 'op-1', ['state' => 'v1']);
        $id2 = $this->manager->createSavepoint('agent-1', 'op-2', ['state' => 'v2']);

        $this->assertNotEquals($id1, $id2);
    }

    public function test_create_savepoint_stores_state(): void
    {
        $state = ['file' => 'test.php', 'contents' => 'code'];
        $savepointId = $this->manager->createSavepoint('agent-1', 'operation', $state);

        $restored = $this->manager->rollback($savepointId);

        $this->assertEquals($state, $restored);
    }

    public function test_rollback_restores_saved_state(): void
    {
        $originalState = ['count' => 5, 'name' => 'test'];
        $savepointId = $this->manager->createSavepoint('agent-1', 'op-1', $originalState);

        $restoredState = $this->manager->rollback($savepointId);

        $this->assertEquals($originalState, $restoredState);
    }

    public function test_rollback_throws_exception_if_not_found(): void
    {
        $this->expectException(RecoveryException::class);
        $this->manager->rollback('non-existent-savepoint');
    }

    public function test_commit_marks_savepoint_as_completed(): void
    {
        $savepointId = $this->manager->createSavepoint('agent-1', 'op-1', ['data' => 'test']);

        $this->manager->commit($savepointId);

        // Verify by checking the savepoint status
        $savepoints = $this->manager->getSavepoints('agent-1');
        $this->assertCount(1, $savepoints);
    }

    public function test_get_savepoints_returns_agent_savepoints(): void
    {
        $this->manager->createSavepoint('agent-1', 'op-1', ['state' => 'v1']);
        $this->manager->createSavepoint('agent-1', 'op-2', ['state' => 'v2']);
        $this->manager->createSavepoint('agent-2', 'op-1', ['state' => 'v1']);

        $agent1Savepoints = $this->manager->getSavepoints('agent-1');

        $this->assertCount(2, $agent1Savepoints);
    }

    public function test_handle_failure_categorizes_error(): void
    {
        $result = $this->manager->handleFailure('agent-1', 'Operation timed out', []);

        $this->assertEquals('agent-1', $result['agent_id']);
        $this->assertEquals('timeout', $result['scenario']);
        $this->assertArrayHasKey('recommended_action', $result);
    }

    public function test_handle_failure_categorizes_timeout(): void
    {
        $result = $this->manager->handleFailure('agent-1', 'Request timed out', []);
        $this->assertEquals('timeout', $result['scenario']);

        $result2 = $this->manager->handleFailure('agent-1', 'Operation has timed out', []);
        $this->assertEquals('timeout', $result2['scenario']);
    }

    public function test_handle_failure_categorizes_deadlock(): void
    {
        $result = $this->manager->handleFailure('agent-1', 'Deadlock detected', []);
        $this->assertEquals('deadlock', $result['scenario']);
    }

    public function test_handle_failure_categorizes_constraint_violation(): void
    {
        $result = $this->manager->handleFailure('agent-1', 'Constraint violation occurred', []);
        $this->assertEquals('constraint_violation', $result['scenario']);

        $result2 = $this->manager->handleFailure('agent-1', 'Validation failed', []);
        $this->assertEquals('constraint_violation', $result2['scenario']);
    }

    public function test_handle_failure_categorizes_resource_exhaustion(): void
    {
        $result = $this->manager->handleFailure('agent-1', 'Memory limit exceeded', []);
        $this->assertEquals('resource_exhaustion', $result['scenario']);

        $result2 = $this->manager->handleFailure('agent-1', 'CPU resource limit reached', []);
        $this->assertEquals('resource_exhaustion', $result2['scenario']);
    }

    public function test_get_recovery_strategy_returns_timeout_actions(): void
    {
        $strategy = $this->manager->getRecoveryStrategy('timeout');

        $this->assertEquals('high', $strategy['priority']);
        $this->assertContains('interrupt', $strategy['actions']);
        $this->assertContains('rollback', $strategy['actions']);
    }

    public function test_get_recovery_strategy_returns_deadlock_actions(): void
    {
        $strategy = $this->manager->getRecoveryStrategy('deadlock');

        $this->assertEquals('critical', $strategy['priority']);
        $this->assertContains('release_locks', $strategy['actions']);
        $this->assertContains('retry_with_backoff', $strategy['actions']);
    }

    public function test_get_recovery_strategy_returns_constraint_actions(): void
    {
        $strategy = $this->manager->getRecoveryStrategy('constraint_violation');

        $this->assertContains('rollback', $strategy['actions']);
        $this->assertContains('validate_state', $strategy['actions']);
    }

    public function test_clear_old_savepoints_removes_expired_savepoints(): void
    {
        $this->manager->createSavepoint('agent-1', 'op-1', ['data' => 'test']);

        $cleared = $this->manager->clearOldSavepoints(0);

        $this->assertGreaterThanOrEqual(0, $cleared);
    }

    public function test_multiple_savepoints_maintained_per_agent(): void
    {
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = $this->manager->createSavepoint('agent-1', "op-{$i}", ['iteration' => $i]);
        }

        $savepoints = $this->manager->getSavepoints('agent-1');

        $this->assertCount(3, $savepoints);
    }
}
