<?php

namespace App\TitanOS\Safety\AuditLogs;

use App\TitanOS\Safety\Contracts\AuditLogContract;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AuditLogger implements AuditLogContract
{
    private const LOG_PREFIX = 'titan:audit:log:';
    private const SECURITY_PREFIX = 'titan:audit:security:';
    private array $logs = [];
    private array $securityLogs = [];

    public function logAction(string $agentId, string $action, array $details, string $status = 'success'): string
    {
        $logId = Str::uuid()->toString();

        $logEntry = [
            'id' => $logId,
            'agent_id' => $agentId,
            'action' => $action,
            'details' => $details,
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
        ];

        if (!isset($this->logs[$agentId])) {
            $this->logs[$agentId] = [];
        }

        $this->logs[$agentId][] = $logEntry;
        Cache::put(self::LOG_PREFIX . $agentId, $this->logs[$agentId], 604800);

        return $logId;
    }

    public function logSecurityEvent(string $agentId, string $eventType, array $context): string
    {
        $eventId = Str::uuid()->toString();

        $event = [
            'id' => $eventId,
            'agent_id' => $agentId,
            'event_type' => $eventType,
            'context' => $context,
            'severity' => $this->determineSeverity($eventType),
            'timestamp' => now()->toIso8601String(),
        ];

        if (!isset($this->securityLogs[$agentId])) {
            $this->securityLogs[$agentId] = [];
        }

        $this->securityLogs[$agentId][] = $event;
        Cache::put(self::SECURITY_PREFIX . $agentId, $this->securityLogs[$agentId], 604800);

        return $eventId;
    }

    public function getAuditTrail(string $agentId, array $filters = []): array
    {
        $logs = $this->logs[$agentId] ?? Cache::get(self::LOG_PREFIX . $agentId, []);

        if (isset($filters['action'])) {
            $logs = array_filter($logs, fn($log) => $log['action'] === $filters['action']);
        }

        if (isset($filters['status'])) {
            $logs = array_filter($logs, fn($log) => $log['status'] === $filters['status']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $logs = array_filter($logs, fn($log) => 
                $log['timestamp'] >= $filters['start_date'] && $log['timestamp'] <= $filters['end_date']
            );
        }

        return array_values($logs);
    }

    public function getComplianceReport(array $filters = []): array
    {
        $violations = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($this->securityLogs as $agentId => $logs) {
            foreach ($logs as $event) {
                if (in_array($event['event_type'], ['access_denied', 'policy_violation', 'limit_exceeded'])) {
                    $violations[] = [
                        'agent_id' => $agentId,
                        'event_type' => $event['event_type'],
                        'timestamp' => $event['timestamp'],
                        'severity' => $event['severity'],
                    ];
                }
            }
        }

        foreach ($this->logs as $agentId => $logs) {
            foreach ($logs as $log) {
                if ($log['status'] === 'success') {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }
        }

        return [
            'total_violations' => count($violations),
            'violations' => $violations,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'success_rate' => $successCount + $failureCount > 0 
                ? round(($successCount / ($successCount + $failureCount)) * 100, 2) 
                : 0,
        ];
    }

    public function archiveLogs(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);
        $archived = 0;

        foreach ($this->logs as $agentId => $logs) {
            $remaining = array_filter($logs, fn($log) => $log['timestamp'] > $cutoffDate->toIso8601String());
            $archived += count($logs) - count($remaining);
            $this->logs[$agentId] = array_values($remaining);
        }

        return $archived;
    }

    public function getStatistics(string $agentId): array
    {
        $logs = $this->logs[$agentId] ?? Cache::get(self::LOG_PREFIX . $agentId, []);
        $securityLogs = $this->securityLogs[$agentId] ?? Cache::get(self::SECURITY_PREFIX . $agentId, []);

        $actions = [];
        $successCount = 0;

        foreach ($logs as $log) {
            $actions[$log['action']] = ($actions[$log['action']] ?? 0) + 1;
            if ($log['status'] === 'success') {
                $successCount++;
            }
        }

        return [
            'agent_id' => $agentId,
            'total_logs' => count($logs),
            'total_security_events' => count($securityLogs),
            'success_rate' => count($logs) > 0 ? round(($successCount / count($logs)) * 100, 2) : 0,
            'common_actions' => array_slice($actions, 0, 5, true),
            'log_count' => count($logs),
        ];
    }

    private function determineSeverity(string $eventType): string
    {
        return match($eventType) {
            'access_denied' => 'high',
            'policy_violation' => 'high',
            'limit_exceeded' => 'medium',
            'rate_limit' => 'medium',
            'error' => 'low',
            default => 'info',
        };
    }
}
