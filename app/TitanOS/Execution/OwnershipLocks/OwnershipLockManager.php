<?php

namespace App\TitanOS\Execution\OwnershipLocks;

use App\TitanOS\Execution\Contracts\OwnershipLockContract;
use App\TitanOS\Execution\Exceptions\{LockConflictException, LockNotHeldException};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OwnershipLockManager implements OwnershipLockContract
{
    private const LOCK_PREFIX = 'titan:lock:';
    private const LOCK_TTL = 3600;
    private array $locks = [];

    public function acquireLock(string|array $filePaths, string $agentId, int $durationSeconds = 3600): array
    {
        $paths = is_array($filePaths) ? $filePaths : [$filePaths];
        $tokens = [];

        foreach ($paths as $path) {
            // Check if locked by another agent
            $lockInfo = $this->isLocked($path);
            if ($lockInfo && $lockInfo['holder'] !== $agentId) {
                throw new LockConflictException(
                    "File locked by {$lockInfo['holder']}",
                    ['file' => $path, 'holder' => $lockInfo['holder']]
                );
            }

            // Create lock token
            $token = Str::uuid()->toString();
            $lockKey = self::LOCK_PREFIX . md5($path);

            $lockData = [
                'token' => $token,
                'holder' => $agentId,
                'file' => $path,
                'acquired_at' => now()->toIso8601String(),
                'expires_at' => now()->addSeconds($durationSeconds)->toIso8601String(),
            ];

            // Store in cache and in-memory
            Cache::put($lockKey, $lockData, $durationSeconds);
            $this->locks[$lockKey] = $lockData;

            $tokens[] = $token;
        }

        return $tokens;
    }

    public function releaseLock(string|array $filePaths, string $agentId): void
    {
        $paths = is_array($filePaths) ? $filePaths : [$filePaths];

        foreach ($paths as $path) {
            $lockKey = self::LOCK_PREFIX . md5($path);
            $lockInfo = $this->locks[$lockKey] ?? null;

            if (!$lockInfo || !$lockInfo['holder']) {
                throw new LockNotHeldException("File not locked: {$path}");
            }

            if ($lockInfo['holder'] !== $agentId) {
                throw new LockNotHeldException("Lock held by {$lockInfo['holder']}, not {$agentId}");
            }

            Cache::forget($lockKey);
            unset($this->locks[$lockKey]);
        }
    }

    public function isLocked(string $filePath): array
    {
        $lockKey = self::LOCK_PREFIX . md5($filePath);
        $lockInfo = $this->locks[$lockKey] ?? Cache::get($lockKey);

        if (!$lockInfo) {
            return [];
        }

        // Check if expired
        $expiresAt = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $lockInfo['expires_at']);
        if ($expiresAt && $expiresAt < now()) {
            unset($this->locks[$lockKey]);
            Cache::forget($lockKey);
            return [];
        }

        return [
            'holder' => $lockInfo['holder'],
            'acquired_at' => $lockInfo['acquired_at'],
            'expires_at' => $lockInfo['expires_at'],
        ];
    }

    public function getLockHolder(string $filePath): ?string
    {
        $lock = $this->isLocked($filePath);
        return $lock['holder'] ?? null;
    }

    public function getAgentLocks(string $agentId): array
    {
        $agentLocks = [];

        foreach ($this->locks as $lockKey => $lockInfo) {
            if ($lockInfo['holder'] === $agentId) {
                // Verify not expired
                $expiresAt = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $lockInfo['expires_at']);
                if ($expiresAt && $expiresAt > now()) {
                    $agentLocks[] = $lockInfo['file'];
                }
            }
        }

        return $agentLocks;
    }

    public function renewLock(string $filePath, string $agentId, int $durationSeconds = 3600): void
    {
        $lockKey = self::LOCK_PREFIX . md5($filePath);
        $lockInfo = $this->locks[$lockKey] ?? null;

        if (!$lockInfo || !$lockInfo['holder']) {
            throw new LockNotHeldException("File not locked: {$filePath}");
        }

        if ($lockInfo['holder'] !== $agentId) {
            throw new LockNotHeldException("Lock held by {$lockInfo['holder']}, not {$agentId}");
        }

        // Update expiration
        $lockInfo['expires_at'] = now()->addSeconds($durationSeconds)->toIso8601String();
        Cache::put($lockKey, $lockInfo, $durationSeconds);
        $this->locks[$lockKey] = $lockInfo;
    }

    public function forceRelease(string $filePath, string $reason): void
    {
        $lockKey = self::LOCK_PREFIX . md5($filePath);
        Cache::forget($lockKey);
        unset($this->locks[$lockKey]);
    }

    public function checkConflict(string $file1, string $file2): array
    {
        $lock1 = $this->isLocked($file1);
        $lock2 = $this->isLocked($file2);

        if (empty($lock1) || empty($lock2)) {
            return [];
        }

        if ($lock1['holder'] === $lock2['holder']) {
            return [
                'same_holder' => true,
                'holder' => $lock1['holder'],
                'files' => [$file1, $file2],
            ];
        }

        return [
            'conflict' => true,
            'file1' => ['file' => $file1, 'holder' => $lock1['holder']],
            'file2' => ['file' => $file2, 'holder' => $lock2['holder']],
        ];
    }
}
