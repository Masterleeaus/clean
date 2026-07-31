<?php

namespace App\TitanOS\Safety\Contracts;

interface AuditLogContract
{
    /**
     * Log agent action.
     *
     * @param string $agentId Agent performing action
     * @param string $action Action type
     * @param array $details Action details and outcome
     * @param string $status success|failure
     * @return string Log entry ID
     */
    public function logAction(string $agentId, string $action, array $details, string $status = 'success'): string;

    /**
     * Log security event.
     *
     * @param string $agentId
     * @param string $eventType Event type (access_denied, policy_violation, etc)
     * @param array $context Event context
     * @return string Event log ID
     */
    public function logSecurityEvent(string $agentId, string $eventType, array $context): string;

    /**
     * Get audit trail for agent.
     *
     * @param string $agentId
     * @param array $filters Date range, action type filters
     * @return array Audit log entries
     */
    public function getAuditTrail(string $agentId, array $filters = []): array;

    /**
     * Get compliance report.
     *
     * @param array $filters Date range, agent filters
     * @return array Compliance violations and summaries
     */
    public function getComplianceReport(array $filters = []): array;

    /**
     * Archive old logs.
     *
     * @param int $daysOld Archive logs older than this many days
     * @return int Number of logs archived
     */
    public function archiveLogs(int $daysOld = 90): int;

    /**
     * Get log statistics.
     *
     * @param string $agentId
     * @return array Log counts, success rate, common actions
     */
    public function getStatistics(string $agentId): array;
}
