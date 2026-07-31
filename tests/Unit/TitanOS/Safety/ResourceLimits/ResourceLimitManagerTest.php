<?php

namespace Tests\Unit\TitanOS\Safety\ResourceLimits;

use App\TitanOS\Safety\ResourceLimits\ResourceLimitManager;
use PHPUnit\Framework\TestCase;

class ResourceLimitManagerTest extends TestCase
{
    private ResourceLimitManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new ResourceLimitManager();
    }

    public function test_set_agent_limits_stores_configuration(): void
    {
        $limits = [
            'cpu_percent' => 50,
            'memory_mb' => 1024,
        ];

        $this->manager->setAgentLimits('agent-1', $limits);
        $result = $this->manager->getAgentLimits('agent-1');

        $this->assertEquals(50, $result['limits']['cpu_percent']);
        $this->assertEquals(1024, $result['limits']['memory_mb']);
    }

    public function test_get_agent_limits_returns_merged_with_defaults(): void
    {
        $limits = ['cpu_percent' => 75];

        $this->manager->setAgentLimits('agent-1', $limits);
        $result = $this->manager->getAgentLimits('agent-1');

        $this->assertEquals(75, $result['limits']['cpu_percent']);
        $this->assertArrayHasKey('memory_mb', $result['limits']);
        $this->assertArrayHasKey('execution_time_seconds', $result['limits']);
    }

    public function test_record_usage_accumulates_resource_usage(): void
    {
        $this->manager->setAgentLimits('agent-1', ['memory_mb' => 512]);
        $this->manager->recordUsage('agent-1', ['memory_mb' => 100]);
        $this->manager->recordUsage('agent-1', ['memory_mb' => 50]);

        $stats = $this->manager->getUsageStats('agent-1');

        $this->assertEquals(150, $stats['current_usage']['memory_mb']);
    }

    public function test_check_violations_detects_exceeded_limits(): void
    {
        $this->manager->setAgentLimits('agent-1', ['memory_mb' => 512]);
        $this->manager->recordUsage('agent-1', ['memory_mb' => 600]);

        $violations = $this->manager->checkViolations('agent-1');

        $this->assertCount(1, $violations);
        $this->assertEquals('memory_mb', $violations[0]['resource']);
        $this->assertEquals(88, $violations[0]['exceeded_by']);
    }

    public function test_check_violations_returns_empty_if_no_violations(): void
    {
        $this->manager->setAgentLimits('agent-1', ['memory_mb' => 512]);
        $this->manager->recordUsage('agent-1', ['memory_mb' => 256]);

        $violations = $this->manager->checkViolations('agent-1');

        $this->assertEmpty($violations);
    }

    public function test_get_usage_stats_calculates_utilization_percentage(): void
    {
        $this->manager->setAgentLimits('agent-1', ['memory_mb' => 1024]);
        $this->manager->recordUsage('agent-1', ['memory_mb' => 512]);

        $stats = $this->manager->getUsageStats('agent-1');

        $this->assertEquals(50.0, $stats['utilization']['memory_mb']);
    }

    public function test_reset_usage_clears_accumulated_usage(): void
    {
        $this->manager->setAgentLimits('agent-1', ['memory_mb' => 512]);
        $this->manager->recordUsage('agent-1', ['memory_mb' => 256]);

        $this->manager->resetUsage('agent-1');

        $stats = $this->manager->getUsageStats('agent-1');
        $this->assertEquals(0, $stats['current_usage']['memory_mb']);
    }

    public function test_multiple_resources_tracked_independently(): void
    {
        $this->manager->setAgentLimits('agent-1', [
            'cpu_percent' => 80,
            'memory_mb' => 512,
            'file_operations' => 1000,
        ]);

        $this->manager->recordUsage('agent-1', [
            'cpu_percent' => 50,
            'memory_mb' => 256,
            'file_operations' => 500,
        ]);

        $stats = $this->manager->getUsageStats('agent-1');

        $this->assertEquals(50, $stats['current_usage']['cpu_percent']);
        $this->assertEquals(256, $stats['current_usage']['memory_mb']);
        $this->assertEquals(500, $stats['current_usage']['file_operations']);
    }
}
