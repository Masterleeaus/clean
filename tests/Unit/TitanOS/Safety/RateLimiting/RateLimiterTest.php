<?php

namespace Tests\Unit\TitanOS\Safety\RateLimiting;

use App\TitanOS\Safety\RateLimiting\RateLimiter;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private RateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limiter = new RateLimiter();
    }

    public function test_set_limit_configures_rate_limit(): void
    {
        $this->limiter->setLimit('agent-1', 'api_call', 10, 60);

        $status = $this->limiter->getStatus('agent-1', 'api_call');

        $this->assertEquals(10, $status['limit']);
        $this->assertEquals(60, $status['window_seconds']);
    }

    public function test_is_allowed_returns_true_when_under_limit(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 3, 60);

        $this->assertTrue($this->limiter->isAllowed('agent-1', 'action'));
        $this->assertTrue($this->limiter->isAllowed('agent-1', 'action'));
        $this->assertTrue($this->limiter->isAllowed('agent-1', 'action'));
    }

    public function test_is_allowed_returns_false_when_limit_exceeded(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 2, 60);

        $this->limiter->recordAction('agent-1', 'action');
        $this->limiter->recordAction('agent-1', 'action');

        $this->assertFalse($this->limiter->isAllowed('agent-1', 'action'));
    }

    public function test_record_action_tracks_attempts(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 10, 60);

        $this->limiter->recordAction('agent-1', 'action', true);
        $this->limiter->recordAction('agent-1', 'action', true);

        $status = $this->limiter->getStatus('agent-1', 'action');

        $this->assertEquals(2, $status['current_usage']);
    }

    public function test_record_blocked_action_increments_blocked_count(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 1, 60);

        $this->limiter->recordAction('agent-1', 'action', true);
        $this->limiter->recordAction('agent-1', 'action', false);

        $status = $this->limiter->getStatus('agent-1', 'action');

        $this->assertEquals(1, $status['blocked_count']);
    }

    public function test_get_status_shows_remaining_quota(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 10, 60);

        $this->limiter->recordAction('agent-1', 'action');
        $this->limiter->recordAction('agent-1', 'action');
        $this->limiter->recordAction('agent-1', 'action');

        $status = $this->limiter->getStatus('agent-1', 'action');

        $this->assertEquals(7, $status['remaining']);
    }

    public function test_get_violations_returns_exceeded_actions(): void
    {
        $this->limiter->setLimit('agent-1', 'action1', 1, 60);
        $this->limiter->setLimit('agent-1', 'action2', 5, 60);

        $this->limiter->recordAction('agent-1', 'action1', false);
        $this->limiter->recordAction('agent-1', 'action1', false);

        $violations = $this->limiter->getViolations('agent-1');

        $this->assertCount(1, $violations);
        $this->assertEquals('action1', $violations[0]['action']);
    }

    public function test_reset_clears_specific_action(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 3, 60);
        $this->limiter->recordAction('agent-1', 'action');
        $this->limiter->recordAction('agent-1', 'action');

        $this->limiter->reset('agent-1', 'action');

        $status = $this->limiter->getStatus('agent-1', 'action');
        $this->assertEquals(0, $status['current_usage']);
    }

    public function test_reset_clears_all_actions(): void
    {
        $this->limiter->setLimit('agent-1', 'action1', 5, 60);
        $this->limiter->setLimit('agent-1', 'action2', 5, 60);

        $this->limiter->recordAction('agent-1', 'action1');
        $this->limiter->recordAction('agent-1', 'action2');

        $this->limiter->reset('agent-1');

        $violations = $this->limiter->getViolations('agent-1');
        $this->assertEmpty($violations);
    }

    public function test_unlimited_action_always_allowed(): void
    {
        // Action without limits
        $allowed = $this->limiter->isAllowed('agent-1', 'unlimited_action');

        $this->assertTrue($allowed);
    }

    public function test_multiple_agents_tracked_independently(): void
    {
        $this->limiter->setLimit('agent-1', 'action', 1, 60);
        $this->limiter->setLimit('agent-2', 'action', 5, 60);

        $this->limiter->recordAction('agent-1', 'action');
        $this->limiter->recordAction('agent-2', 'action');
        $this->limiter->recordAction('agent-2', 'action');

        $status1 = $this->limiter->getStatus('agent-1', 'action');
        $status2 = $this->limiter->getStatus('agent-2', 'action');

        $this->assertEquals(1, $status1['current_usage']);
        $this->assertEquals(2, $status2['current_usage']);
    }
}
