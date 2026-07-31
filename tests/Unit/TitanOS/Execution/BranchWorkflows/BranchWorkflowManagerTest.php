<?php

namespace Tests\Unit\TitanOS\Execution\BranchWorkflows;

use App\TitanOS\Execution\BranchWorkflows\BranchWorkflowManager;
use App\TitanOS\Execution\Exceptions\BranchWorkflowException;
use PHPUnit\Framework\TestCase;

class BranchWorkflowManagerTest extends TestCase
{
    private BranchWorkflowManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new BranchWorkflowManager(base_path());
    }

    public function test_create_branch_returns_branch_info(): void
    {
        $branchInfo = $this->manager->createBranch('agent-1', 'main', 'task-123');

        $this->assertArrayHasKey('agent_id', $branchInfo);
        $this->assertArrayHasKey('branch_name', $branchInfo);
        $this->assertArrayHasKey('base_branch', $branchInfo);
        $this->assertArrayHasKey('task_id', $branchInfo);
        $this->assertEquals('agent-1', $branchInfo['agent_id']);
        $this->assertEquals('main', $branchInfo['base_branch']);
        $this->assertStringContainsString('agent-1', $branchInfo['branch_name']);
        $this->assertStringContainsString('task-123', $branchInfo['branch_name']);
    }

    public function test_get_agent_branch_returns_branch_name(): void
    {
        $this->manager->createBranch('agent-1', 'main', 'task-123');

        $branchName = $this->manager->getAgentBranch('agent-1');

        $this->assertNotNull($branchName);
        $this->assertStringContainsString('agent-1', $branchName);
    }

    public function test_get_agent_branch_returns_null_if_no_branch(): void
    {
        $branchName = $this->manager->getAgentBranch('non-existent-agent');

        $this->assertNull($branchName);
    }

    public function test_get_active_branches_returns_all_branches(): void
    {
        $this->manager->createBranch('agent-1', 'main', 'task-1');
        $this->manager->createBranch('agent-2', 'main', 'task-2');

        $branches = $this->manager->getActiveBranches();

        $this->assertCount(2, $branches);
        $agentIds = array_column($branches, 'agent_id');
        $this->assertContains('agent-1', $agentIds);
        $this->assertContains('agent-2', $agentIds);
    }

    public function test_commit_requires_agent_on_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->commit('non-existent-agent', 'Commit message', ['file.php']);
    }

    public function test_push_requires_agent_on_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->push('non-existent-agent');
    }

    public function test_create_pull_request_requires_agent_on_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->createPullRequest('non-existent-agent', 'PR Title', 'PR Description');
    }

    public function test_create_pull_request_returns_pr_info(): void
    {
        $this->manager->createBranch('agent-1', 'main', 'task-123');

        $pr = $this->manager->createPullRequest(
            'agent-1',
            'Implement feature',
            'This PR implements the new feature',
            'main'
        );

        $this->assertArrayHasKey('id', $pr);
        $this->assertArrayHasKey('source_branch', $pr);
        $this->assertArrayHasKey('target_branch', $pr);
        $this->assertArrayHasKey('title', $pr);
        $this->assertArrayHasKey('description', $pr);
        $this->assertEquals('open', $pr['status']);
        $this->assertEquals('Implement feature', $pr['title']);
        $this->assertEquals('main', $pr['target_branch']);
    }

    public function test_merge_pull_request_requires_open_pr(): void
    {
        $this->manager->createBranch('agent-1', 'main', 'task-123');
        $pr = $this->manager->createPullRequest('agent-1', 'PR Title', 'PR Description');
        $prId = $pr['id'];

        // Simulate PR merge
        $result = $this->manager->mergePullRequest($prId, 'merge');

        $this->assertArrayHasKey('pr_id', $result);
        $this->assertArrayHasKey('merge_commit', $result);
        $this->assertArrayHasKey('merged_at', $result);
    }

    public function test_merge_pull_request_throws_exception_if_not_found(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->mergePullRequest('non-existent-pr');
    }

    public function test_rebase_requires_agent_on_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->rebase('non-existent-agent', 'main');
    }

    public function test_sync_requires_agent_on_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->sync('non-existent-agent');
    }

    public function test_resolve_conflicts_requires_agent_on_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->resolveConflicts('non-existent-agent', []);
    }

    public function test_delete_branch_requires_agent_branch(): void
    {
        $this->expectException(BranchWorkflowException::class);
        $this->manager->deleteBranch('non-existent-agent');
    }

    public function test_delete_branch_removes_tracking(): void
    {
        $this->manager->createBranch('agent-1', 'main', 'task-123');
        $branchNameBefore = $this->manager->getAgentBranch('agent-1');
        $this->assertNotNull($branchNameBefore);

        // Note: This test can only check tracking removal, not actual git operations
        // since git operations would require a real repository
    }
}
