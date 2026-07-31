<?php

namespace App\TitanOS\Execution\BranchWorkflows;

use App\TitanOS\Execution\Contracts\BranchWorkflowContract;
use App\TitanOS\Execution\Exceptions\BranchWorkflowException;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BranchWorkflowManager implements BranchWorkflowContract
{
    private array $agentBranches = [];
    private array $pullRequests = [];
    private string $repositoryPath;

    public function __construct(string $repositoryPath = '.')
    {
        $this->repositoryPath = $repositoryPath;
    }

    public function createBranch(string $agentId, string $baseBranch, string $taskId): array
    {
        $timestamp = now()->timestamp;
        $branchName = "agent_{$agentId}_{$taskId}_{$timestamp}";

        try {
            $this->executeGit(['checkout', $baseBranch]);
            $this->executeGit(['pull', 'origin', $baseBranch]);
            $this->executeGit(['checkout', '-b', $branchName]);

            $this->agentBranches[$agentId] = [
                'agent_id' => $agentId,
                'branch_name' => $branchName,
                'base_branch' => $baseBranch,
                'task_id' => $taskId,
                'created_at' => now()->toIso8601String(),
                'commits' => 0,
            ];

            return $this->agentBranches[$agentId];
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to create branch: {$e->getMessage()}");
        }
    }

    public function getAgentBranch(string $agentId): ?string
    {
        return $this->agentBranches[$agentId]['branch_name'] ?? null;
    }

    public function getActiveBranches(): array
    {
        $branches = [];

        foreach ($this->agentBranches as $agentId => $branchInfo) {
            $branches[] = $branchInfo;
        }

        return $branches;
    }

    public function commit(string $agentId, string $message, array $files): string
    {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent not on a branch: {$agentId}");
        }

        try {
            foreach ($files as $file) {
                $this->executeGit(['add', $file]);
            }

            $this->executeGit(['commit', '-m', $message]);

            $commitHash = trim($this->executeGit(['rev-parse', 'HEAD']));
            $this->agentBranches[$agentId]['commits']++;

            return $commitHash;
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to commit: {$e->getMessage()}");
        }
    }

    public function push(string $agentId, ?string $branch = null): array
    {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent not on a branch: {$agentId}");
        }

        $branchName = $branch ?? $this->agentBranches[$agentId]['branch_name'];

        try {
            $this->executeGit(['push', '-u', 'origin', $branchName]);

            return [
                'branch' => $branchName,
                'remote' => 'origin',
                'pushed_at' => now()->toIso8601String(),
                'tracking' => "origin/{$branchName}",
            ];
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to push: {$e->getMessage()}");
        }
    }

    public function createPullRequest(
        string $agentId,
        string $title,
        string $description,
        string $targetBranch = 'main'
    ): array {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent not on a branch: {$agentId}");
        }

        $prId = Str::uuid()->toString();
        $branchName = $this->agentBranches[$agentId]['branch_name'];

        $this->pullRequests[$prId] = [
            'id' => $prId,
            'source_branch' => $branchName,
            'target_branch' => $targetBranch,
            'title' => $title,
            'description' => $description,
            'status' => 'open',
            'created_at' => now()->toIso8601String(),
            'agent_id' => $agentId,
        ];

        return $this->pullRequests[$prId];
    }

    public function mergePullRequest(string $prId, string $mergeStrategy = 'merge'): array
    {
        if (!isset($this->pullRequests[$prId])) {
            throw new BranchWorkflowException("Pull request not found: {$prId}");
        }

        $pr = &$this->pullRequests[$prId];

        if ($pr['status'] !== 'open') {
            throw new BranchWorkflowException("Pull request not open: {$prId}");
        }

        try {
            $targetBranch = $pr['target_branch'];
            $sourceBranch = $pr['source_branch'];

            $this->executeGit(['checkout', $targetBranch]);
            $this->executeGit(['pull', 'origin', $targetBranch]);

            if ($mergeStrategy === 'squash') {
                $this->executeGit(['merge', '--squash', $sourceBranch]);
                $this->executeGit(['commit', '-m', "Squash merge of {$sourceBranch}"]);
            } elseif ($mergeStrategy === 'rebase') {
                $this->executeGit(['rebase', $sourceBranch]);
            } else {
                $this->executeGit(['merge', '--no-ff', $sourceBranch]);
            }

            $mergeCommit = trim($this->executeGit(['rev-parse', 'HEAD']));
            $this->executeGit(['push', 'origin', $targetBranch]);

            $pr['status'] = 'merged';
            $pr['merge_commit'] = $mergeCommit;
            $pr['merged_at'] = now()->toIso8601String();
            $pr['merge_strategy'] = $mergeStrategy;

            return [
                'pr_id' => $prId,
                'merge_commit' => $mergeCommit,
                'merged_at' => $pr['merged_at'],
                'strategy' => $mergeStrategy,
            ];
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to merge PR: {$e->getMessage()}");
        }
    }

    public function rebase(string $agentId, string $baseBranch): array
    {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent not on a branch: {$agentId}");
        }

        $branchName = $this->agentBranches[$agentId]['branch_name'];

        try {
            $this->executeGit(['fetch', 'origin', $baseBranch]);
            $this->executeGit(['rebase', "origin/{$baseBranch}"]);

            $rebaseCommit = trim($this->executeGit(['rev-parse', 'HEAD']));

            return [
                'branch' => $branchName,
                'rebased_on' => $baseBranch,
                'commit' => $rebaseCommit,
                'rebased_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to rebase: {$e->getMessage()}");
        }
    }

    public function sync(string $agentId): array
    {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent not on a branch: {$agentId}");
        }

        $baseBranch = $this->agentBranches[$agentId]['base_branch'];

        try {
            $this->executeGit(['fetch', 'origin']);
            $this->executeGit(['merge', "origin/{$baseBranch}"]);

            $syncCommit = trim($this->executeGit(['rev-parse', 'HEAD']));

            return [
                'branch' => $this->agentBranches[$agentId]['branch_name'],
                'synced_with' => $baseBranch,
                'commit' => $syncCommit,
                'synced_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to sync: {$e->getMessage()}");
        }
    }

    public function resolveConflicts(string $agentId, array $resolutions): array
    {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent not on a branch: {$agentId}");
        }

        try {
            foreach ($resolutions as $filePath => $strategy) {
                if ($strategy === 'ours') {
                    $this->executeGit(['checkout', '--ours', $filePath]);
                } elseif ($strategy === 'theirs') {
                    $this->executeGit(['checkout', '--theirs', $filePath]);
                }

                $this->executeGit(['add', $filePath]);
            }

            $this->executeGit(['commit', '-m', 'Resolve merge conflicts']);

            return [
                'resolved_files' => array_keys($resolutions),
                'resolved_at' => now()->toIso8601String(),
                'agent_id' => $agentId,
            ];
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to resolve conflicts: {$e->getMessage()}");
        }
    }

    public function deleteBranch(string $agentId, bool $remote = true): void
    {
        if (!isset($this->agentBranches[$agentId])) {
            throw new BranchWorkflowException("Agent branch not found: {$agentId}");
        }

        $branchName = $this->agentBranches[$agentId]['branch_name'];

        try {
            $this->executeGit(['checkout', $this->agentBranches[$agentId]['base_branch']]);
            $this->executeGit(['branch', '-d', $branchName]);

            if ($remote) {
                $this->executeGit(['push', 'origin', '--delete', $branchName]);
            }

            unset($this->agentBranches[$agentId]);
        } catch (\Exception $e) {
            throw new BranchWorkflowException("Failed to delete branch: {$e->getMessage()}");
        }
    }

    private function executeGit(array $args): string
    {
        $process = new Process(array_merge(['git'], $args), $this->repositoryPath);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }

        return $process->getOutput();
    }
}
