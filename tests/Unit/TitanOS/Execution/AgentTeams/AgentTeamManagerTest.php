<?php

namespace Tests\Unit\TitanOS\Execution\AgentTeams;

use App\TitanOS\Execution\AgentTeams\AgentTeamManager;
use App\TitanOS\Execution\Exceptions\AgentTeamException;
use PHPUnit\Framework\TestCase;

class AgentTeamManagerTest extends TestCase
{
    private AgentTeamManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new AgentTeamManager();
    }

    public function test_register_agent_stores_agent_configuration(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', [
            'specializations' => ['php', 'laravel'],
            'capacity' => 5,
        ]);

        $agent = $this->manager->getAgent('agent-1');

        $this->assertEquals('agent-1', $agent['id']);
        $this->assertEquals('developer', $agent['role']);
        $this->assertEquals(['php', 'laravel'], $agent['specializations']);
        $this->assertEquals(5, $agent['capacity']);
    }

    public function test_get_agents_returns_all_agents(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);
        $this->manager->registerAgent('agent-2', 'reviewer', []);
        $this->manager->registerAgent('agent-3', 'developer', []);

        $agents = $this->manager->getAgents();
        $this->assertCount(3, $agents);
    }

    public function test_get_agents_filters_by_role(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);
        $this->manager->registerAgent('agent-2', 'reviewer', []);
        $this->manager->registerAgent('agent-3', 'developer', []);

        $developers = $this->manager->getAgents('developer');
        $this->assertCount(2, $developers);
    }

    public function test_create_team_returns_team_id(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);
        $this->manager->registerAgent('agent-2', 'reviewer', []);

        $teamId = $this->manager->createTeam('Backend Team', 'backend', [
            'agent-1',
            'agent-2',
        ]);

        $this->assertNotEmpty($teamId);
        $teams = $this->manager->getTeams();
        $this->assertCount(1, $teams);
    }

    public function test_get_teams_filters_by_domain(): void
    {
        $backend = $this->manager->createTeam('Backend Team', 'backend', []);
        $frontend = $this->manager->createTeam('Frontend Team', 'frontend', []);

        $backendTeams = $this->manager->getTeams('backend');
        $this->assertCount(1, $backendTeams);
    }

    public function test_select_agents_by_role(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', [
            'specializations' => ['php'],
        ]);
        $this->manager->registerAgent('agent-2', 'reviewer', [
            'specializations' => ['testing'],
        ]);

        $selected = $this->manager->selectAgents(['role' => 'developer']);

        $this->assertCount(1, $selected);
        $this->assertEquals('agent-1', $selected[0]['agent_id']);
    }

    public function test_select_agents_by_capabilities(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', [
            'specializations' => ['php', 'laravel'],
        ]);
        $this->manager->registerAgent('agent-2', 'developer', [
            'specializations' => ['javascript', 'react'],
        ]);

        $selected = $this->manager->selectAgents(['capabilities' => ['php', 'laravel']]);

        $this->assertCount(1, $selected);
        $this->assertEquals('agent-1', $selected[0]['agent_id']);
    }

    public function test_select_agents_sorts_by_confidence_and_workload(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', [
            'specializations' => ['php'],
            'capacity' => 5,
        ]);
        $this->manager->registerAgent('agent-2', 'developer', [
            'specializations' => ['php'],
            'capacity' => 5,
        ]);

        $selected = $this->manager->selectAgents(['role' => 'developer']);

        $this->assertCount(2, $selected);
        $firstScore = $selected[0]['confidence'];
        $secondScore = $selected[1]['confidence'];
        $this->assertGreaterThanOrEqual($secondScore, $firstScore);
    }

    public function test_create_handoff_stores_context(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);
        $this->manager->registerAgent('agent-2', 'reviewer', []);

        $handoff = $this->manager->createHandoff('agent-1', 'agent-2', [
            'task_id' => 'task-123',
            'files' => ['src/app.php'],
        ]);

        $this->assertEquals('agent-1', $handoff['from_agent']);
        $this->assertEquals('agent-2', $handoff['to_agent']);
        $this->assertEquals('pending', $handoff['status']);
        $this->assertEquals('task-123', $handoff['context']['task_id']);
    }

    public function test_accept_handoff_updates_status(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);
        $this->manager->registerAgent('agent-2', 'reviewer', []);

        $handoff = $this->manager->createHandoff('agent-1', 'agent-2', []);
        $handoffId = $handoff['id'];

        $this->manager->acceptHandoff($handoffId, 'agent-2');

        $this->assertEquals('accepted', $handoff['status']);
    }

    public function test_accept_handoff_throws_exception_if_not_authorized(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);
        $this->manager->registerAgent('agent-2', 'reviewer', []);
        $this->manager->registerAgent('agent-3', 'reviewer', []);

        $handoff = $this->manager->createHandoff('agent-1', 'agent-2', []);
        $handoffId = $handoff['id'];

        $this->expectException(AgentTeamException::class);
        $this->manager->acceptHandoff($handoffId, 'agent-3');
    }

    public function test_get_workload_returns_agent_capacity_and_tasks(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', ['capacity' => 5]);

        $taskId = $this->manager->assignTask('agent-1', ['description' => 'task-1']);

        $workload = $this->manager->getWorkload('agent-1');

        $this->assertEquals('agent-1', $workload['agent_id']);
        $this->assertEquals(1, $workload['total_tasks']);
        $this->assertEquals(5, $workload['capacity']);
        $this->assertEquals(0.2, $workload['utilization']);
    }

    public function test_assign_task_creates_task_in_queue(): void
    {
        $this->manager->registerAgent('agent-1', 'developer', []);

        $taskId = $this->manager->assignTask('agent-1', ['description' => 'task-1'], 5);

        $this->assertNotEmpty($taskId);
        $workload = $this->manager->getWorkload('agent-1');
        $this->assertEquals(1, $workload['total_tasks']);
    }

    public function test_assign_task_throws_exception_if_agent_not_found(): void
    {
        $this->expectException(AgentTeamException::class);
        $this->manager->assignTask('non-existent-agent', []);
    }
}
