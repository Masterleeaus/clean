<?php

namespace App\TitanOS\Execution\Contracts;

interface BranchWorkflowContract
{
    /**
     * Create new branch for agent work.
     *
     * @param string $agentId Agent ID
     * @param string $baseBranch Base branch to branch from
     * @param string $taskId Task identifier
     * @return array Branch info with name and details
     */
    public function createBranch(string $agentId, string $baseBranch, string $taskId): array;

    /**
     * Get agent's current branch.
     *
     * @param string $agentId
     * @return string|null Branch name or null if not on branch
     */
    public function getAgentBranch(string $agentId): ?string;

    /**
     * Get all active agent branches.
     *
     * @return array List of agent branches with metadata
     */
    public function getActiveBranches(): array;

    /**
     * Commit work on branch.
     *
     * @param string $agentId Agent ID
     * @param string $message Commit message
     * @param array $files Files to commit
     * @return string Commit hash
     */
    public function commit(string $agentId, string $message, array $files): string;

    /**
     * Push branch to remote.
     *
     * @param string $agentId Agent ID
     * @param string|null $branch Branch to push (current if null)
     * @return array Push result with remote tracking info
     */
    public function push(string $agentId, ?string $branch = null): array;

    /**
     * Create pull request for branch.
     *
     * @param string $agentId Agent ID
     * @param string $title PR title
     * @param string $description PR description
     * @param string $targetBranch Target branch for PR
     * @return array PR info with URL and ID
     */
    public function createPullRequest(
        string $agentId,
        string $title,
        string $description,
        string $targetBranch = 'main'
    ): array;

    /**
     * Merge pull request.
     *
     * @param string $prId Pull request ID or URL
     * @param string $mergeStrategy merge|squash|rebase
     * @return array Merge result with commit hash
     */
    public function mergePullRequest(string $prId, string $mergeStrategy = 'merge'): array;

    /**
     * Rebase branch on base.
     *
     * @param string $agentId Agent ID
     * @param string $baseBranch Base to rebase on
     * @return array Rebase result
     */
    public function rebase(string $agentId, string $baseBranch): array;

    /**
     * Sync agent branch with base.
     *
     * @param string $agentId Agent ID
     * @return array Sync result with new commits
     */
    public function sync(string $agentId): array;

    /**
     * Resolve merge conflicts.
     *
     * @param string $agentId Agent ID
     * @param array $resolutions File path => strategy mappings
     * @return array Conflict resolution result
     */
    public function resolveConflicts(string $agentId, array $resolutions): array;

    /**
     * Delete agent's branch after merge.
     *
     * @param string $agentId Agent ID
     * @param bool $remote Delete from remote too
     * @return void
     */
    public function deleteBranch(string $agentId, bool $remote = true): void;
}
