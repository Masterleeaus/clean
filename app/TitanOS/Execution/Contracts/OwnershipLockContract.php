<?php

namespace App\TitanOS\Execution\Contracts;

interface OwnershipLockContract
{
    /**
     * Acquire lock on file(s) for exclusive editing.
     *
     * @param string|array $filePaths File path(s) to lock
     * @param string $agentId Agent acquiring lock
     * @param int $durationSeconds How long to hold lock
     * @return array Lock tokens for the acquired locks
     *
     * @throws \App\TitanOS\Execution\Exceptions\LockConflictException
     */
    public function acquireLock(string|array $filePaths, string $agentId, int $durationSeconds = 3600): array;

    /**
     * Release lock on file(s).
     *
     * @param string|array $filePaths File path(s) to unlock
     * @param string $agentId Agent releasing lock
     * @return void
     *
     * @throws \App\TitanOS\Execution\Exceptions\LockNotHeldException
     */
    public function releaseLock(string|array $filePaths, string $agentId): void;

    /**
     * Check if file is locked.
     *
     * @param string $filePath
     * @return array Lock info or empty if unlocked
     */
    public function isLocked(string $filePath): array;

    /**
     * Get lock holder for file.
     *
     * @param string $filePath
     * @return string|null Agent ID holding lock or null
     */
    public function getLockHolder(string $filePath): ?string;

    /**
     * Get all locks held by agent.
     *
     * @param string $agentId
     * @return array List of locked files
     */
    public function getAgentLocks(string $agentId): array;

    /**
     * Renew lock to extend duration.
     *
     * @param string $filePath
     * @param string $agentId Agent renewing lock
     * @param int $durationSeconds Extension duration
     * @return void
     *
     * @throws \App\TitanOS\Execution\Exceptions\LockNotHeldException
     */
    public function renewLock(string $filePath, string $agentId, int $durationSeconds = 3600): void;

    /**
     * Force release lock (admin only).
     *
     * @param string $filePath
     * @param string $reason Why lock is being released
     * @return void
     */
    public function forceRelease(string $filePath, string $reason): void;

    /**
     * Get lock conflict between agents.
     *
     * @param string $file1
     * @param string $file2
     * @return array Conflict info or empty if none
     */
    public function checkConflict(string $file1, string $file2): array;
}
