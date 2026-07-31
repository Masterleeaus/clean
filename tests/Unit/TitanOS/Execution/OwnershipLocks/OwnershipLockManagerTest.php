<?php

namespace Tests\Unit\TitanOS\Execution\OwnershipLocks;

use App\TitanOS\Execution\OwnershipLocks\OwnershipLockManager;
use App\TitanOS\Execution\Exceptions\LockConflictException;
use App\TitanOS\Execution\Exceptions\LockNotHeldException;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Cache;

class OwnershipLockManagerTest extends TestCase
{
    private OwnershipLockManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new OwnershipLockManager();
    }

    public function test_acquire_lock_returns_token(): void
    {
        $tokens = $this->manager->acquireLock('file.php', 'agent-1');

        $this->assertCount(1, $tokens);
        $this->assertNotEmpty($tokens[0]);
    }

    public function test_acquire_lock_multiple_files(): void
    {
        $tokens = $this->manager->acquireLock([
            'file1.php',
            'file2.php',
            'file3.php',
        ], 'agent-1');

        $this->assertCount(3, $tokens);
    }

    public function test_is_locked_returns_lock_info(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');

        $lockInfo = $this->manager->isLocked('file.php');

        $this->assertEquals('agent-1', $lockInfo['holder']);
        $this->assertNotEmpty($lockInfo['acquired_at']);
        $this->assertNotEmpty($lockInfo['expires_at']);
    }

    public function test_is_locked_returns_empty_if_not_locked(): void
    {
        $lockInfo = $this->manager->isLocked('unlocked.php');

        $this->assertEmpty($lockInfo);
    }

    public function test_acquire_lock_throws_exception_if_locked_by_other_agent(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');

        $this->expectException(LockConflictException::class);
        $this->manager->acquireLock('file.php', 'agent-2');
    }

    public function test_acquire_lock_allows_same_agent_to_acquire_again(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');
        $tokens = $this->manager->acquireLock('file.php', 'agent-1');

        $this->assertCount(1, $tokens);
    }

    public function test_release_lock_removes_lock(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');
        $this->manager->releaseLock('file.php', 'agent-1');

        $lockInfo = $this->manager->isLocked('file.php');
        $this->assertEmpty($lockInfo);
    }

    public function test_release_lock_throws_exception_if_not_held(): void
    {
        $this->expectException(LockNotHeldException::class);
        $this->manager->releaseLock('file.php', 'agent-1');
    }

    public function test_release_lock_throws_exception_if_held_by_other_agent(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');

        $this->expectException(LockNotHeldException::class);
        $this->manager->releaseLock('file.php', 'agent-2');
    }

    public function test_release_lock_multiple_files(): void
    {
        $this->manager->acquireLock(['file1.php', 'file2.php'], 'agent-1');
        $this->manager->releaseLock(['file1.php', 'file2.php'], 'agent-1');

        $this->assertEmpty($this->manager->isLocked('file1.php'));
        $this->assertEmpty($this->manager->isLocked('file2.php'));
    }

    public function test_get_lock_holder_returns_agent_id(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');

        $holder = $this->manager->getLockHolder('file.php');

        $this->assertEquals('agent-1', $holder);
    }

    public function test_get_lock_holder_returns_null_if_unlocked(): void
    {
        $holder = $this->manager->getLockHolder('file.php');

        $this->assertNull($holder);
    }

    public function test_get_agent_locks_returns_files_held_by_agent(): void
    {
        $this->manager->acquireLock(['file1.php', 'file2.php'], 'agent-1');
        $this->manager->acquireLock('file3.php', 'agent-2');

        $agentLocks = $this->manager->getAgentLocks('agent-1');

        $this->assertCount(2, $agentLocks);
        $this->assertContains('file1.php', $agentLocks);
        $this->assertContains('file2.php', $agentLocks);
    }

    public function test_renew_lock_extends_expiration(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1', 3600);
        $originalLockInfo = $this->manager->isLocked('file.php');

        $this->manager->renewLock('file.php', 'agent-1', 7200);

        $renewedLockInfo = $this->manager->isLocked('file.php');
        $this->assertNotEqual(
            $originalLockInfo['expires_at'],
            $renewedLockInfo['expires_at']
        );
    }

    public function test_renew_lock_throws_exception_if_not_held(): void
    {
        $this->expectException(LockNotHeldException::class);
        $this->manager->renewLock('file.php', 'agent-1');
    }

    public function test_renew_lock_throws_exception_if_held_by_other_agent(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');

        $this->expectException(LockNotHeldException::class);
        $this->manager->renewLock('file.php', 'agent-2');
    }

    public function test_force_release_removes_lock(): void
    {
        $this->manager->acquireLock('file.php', 'agent-1');

        $this->manager->forceRelease('file.php', 'Lock timeout');

        $this->assertEmpty($this->manager->isLocked('file.php'));
    }

    public function test_check_conflict_detects_same_holder(): void
    {
        $this->manager->acquireLock(['file1.php', 'file2.php'], 'agent-1');

        $conflict = $this->manager->checkConflict('file1.php', 'file2.php');

        $this->assertTrue($conflict['same_holder']);
        $this->assertEquals('agent-1', $conflict['holder']);
    }

    public function test_check_conflict_detects_different_holders(): void
    {
        $this->manager->acquireLock('file1.php', 'agent-1');
        $this->manager->acquireLock('file2.php', 'agent-2');

        $conflict = $this->manager->checkConflict('file1.php', 'file2.php');

        $this->assertTrue($conflict['conflict']);
        $this->assertEquals('agent-1', $conflict['file1']['holder']);
        $this->assertEquals('agent-2', $conflict['file2']['holder']);
    }

    public function test_check_conflict_returns_empty_if_no_conflict(): void
    {
        $conflict = $this->manager->checkConflict('file1.php', 'file2.php');

        $this->assertEmpty($conflict);
    }
}
