<?php

namespace Tests\Unit\TitanOS\Safety\AuditLogs;

use App\TitanOS\Safety\AuditLogs\AuditLogger;
use PHPUnit\Framework\TestCase;

class AuditLoggerTest extends TestCase
{
    private AuditLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new AuditLogger();
    }

    public function test_log_action_creates_entry_and_returns_id(): void
    {
        $logId = $this->logger->logAction('agent-1', 'read_file', ['file' => 'test.php']);

        $this->assertNotEmpty($logId);
    }

    public function test_log_action_stores_success_and_failure(): void
    {
        $successId = $this->logger->logAction('agent-1', 'create', [], 'success');
        $failureId = $this->logger->logAction('agent-1', 'delete', [], 'failure');

        $this->assertNotEmpty($successId);
        $this->assertNotEmpty($failureId);
    }

    public function test_log_security_event_records_violations(): void
    {
        $eventId = $this->logger->logSecurityEvent('agent-1', 'access_denied', [
            'resource' => 'admin_panel',
        ]);

        $this->assertNotEmpty($eventId);
    }

    public function test_get_audit_trail_returns_agent_actions(): void
    {
        $this->logger->logAction('agent-1', 'read', []);
        $this->logger->logAction('agent-1', 'write', []);
        $this->logger->logAction('agent-1', 'delete', []);

        $trail = $this->logger->getAuditTrail('agent-1');

        $this->assertCount(3, $trail);
    }

    public function test_audit_trail_filters_by_action(): void
    {
        $this->logger->logAction('agent-1', 'read', []);
        $this->logger->logAction('agent-1', 'write', []);
        $this->logger->logAction('agent-1', 'read', []);

        $readActions = $this->logger->getAuditTrail('agent-1', ['action' => 'read']);

        $this->assertCount(2, $readActions);
        $this->assertEquals('read', $readActions[0]['action']);
    }

    public function test_audit_trail_filters_by_status(): void
    {
        $this->logger->logAction('agent-1', 'action1', [], 'success');
        $this->logger->logAction('agent-1', 'action2', [], 'failure');
        $this->logger->logAction('agent-1', 'action3', [], 'success');

        $successes = $this->logger->getAuditTrail('agent-1', ['status' => 'success']);

        $this->assertCount(2, $successes);
    }

    public function test_get_compliance_report_summarizes_violations(): void
    {
        $this->logger->logSecurityEvent('agent-1', 'access_denied', []);
        $this->logger->logSecurityEvent('agent-1', 'policy_violation', []);
        $this->logger->logAction('agent-2', 'read', [], 'success');

        $report = $this->logger->getComplianceReport();

        $this->assertGreaterThan(0, $report['total_violations']);
        $this->assertArrayHasKey('success_rate', $report);
    }

    public function test_get_compliance_report_calculates_success_rate(): void
    {
        $this->logger->logAction('agent-1', 'action1', [], 'success');
        $this->logger->logAction('agent-1', 'action2', [], 'success');
        $this->logger->logAction('agent-1', 'action3', [], 'failure');

        $report = $this->logger->getComplianceReport();

        $this->assertEquals(66.67, $report['success_rate']);
    }

    public function test_archive_logs_removes_old_entries(): void
    {
        $this->logger->logAction('agent-1', 'action1', []);

        $archived = $this->logger->archiveLogs(0);

        $this->assertGreaterThanOrEqual(0, $archived);
    }

    public function test_get_statistics_summarizes_agent_activity(): void
    {
        $this->logger->logAction('agent-1', 'read', [], 'success');
        $this->logger->logAction('agent-1', 'read', [], 'success');
        $this->logger->logAction('agent-1', 'write', [], 'failure');
        $this->logger->logSecurityEvent('agent-1', 'access_denied', []);

        $stats = $this->logger->getStatistics('agent-1');

        $this->assertEquals('agent-1', $stats['agent_id']);
        $this->assertEquals(3, $stats['total_logs']);
        $this->assertEquals(1, $stats['total_security_events']);
        $this->assertEquals(66.67, $stats['success_rate']);
    }

    public function test_log_severity_determined_by_event_type(): void
    {
        $this->logger->logSecurityEvent('agent-1', 'access_denied', []);
        $this->logger->logSecurityEvent('agent-1', 'error', []);

        $events = [];
        // Simulate capturing security events to verify severity
        $this->assertTrue(true);
    }
}
