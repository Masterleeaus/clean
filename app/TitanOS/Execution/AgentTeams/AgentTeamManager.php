<?php

namespace App\TitanOS\Execution\AgentTeams;

use App\TitanOS\Execution\Contracts\AgentTeamContract;
use App\TitanOS\Execution\Exceptions\AgentTeamException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AgentTeamManager implements AgentTeamContract
{
    private array $agents = [];
    private array $teams = [];
    private array $handoffs = [];
    private array $taskQueues = [];

    public function registerAgent(string $agentId, string $role, array $config = []): void
    {
        $this->agents[$agentId] = [
            'id' => $agentId,
            'role' => $role,
            'config' => $config,
            'specializations' => $config['specializations'] ?? [],
            'capacity' => $config['capacity'] ?? 5,
            'registered_at' => now(),
        ];
    }

    public function getAgents(?string $role = null): Collection
    {
        $agents = collect($this->agents);

        if ($role) {
            $agents = $agents->filter(fn($a) => $a['role'] === $role);
        }

        return $agents;
    }

    public function getAgent(string $agentId): array
    {
        return $this->agents[$agentId] ?? [];
    }

    public function createTeam(string $teamName, string $domain, array $agents): string
    {
        $teamId = Str::uuid()->toString();

        $this->teams[$teamId] = [
            'id' => $teamId,
            'name' => $teamName,
            'domain' => $domain,
            'agents' => $agents,
            'created_at' => now(),
        ];

        return $teamId;
    }

    public function getTeams(?string $domain = null): Collection
    {
        $teams = collect($this->teams);

        if ($domain) {
            $teams = $teams->filter(fn($t) => $t['domain'] === $domain);
        }

        return $teams;
    }

    public function selectAgents(array $taskRequirements, ?string $domain = null): array
    {
        $selectedAgents = [];

        // Find agents matching requirements
        foreach ($this->agents as $agentId => $agent) {
            $score = $this->calculateAgentScore($agent, $taskRequirements, $domain);

            if ($score > 0) {
                $selectedAgents[] = [
                    'agent_id' => $agentId,
                    'role' => $agent['role'],
                    'confidence' => $score,
                    'workload' => count($this->taskQueues[$agentId] ?? []),
                ];
            }
        }

        // Sort by score and workload
        usort($selectedAgents, function ($a, $b) {
            $scoreCompare = $b['confidence'] <=> $a['confidence'];
            if ($scoreCompare !== 0) {
                return $scoreCompare;
            }
            return $a['workload'] <=> $b['workload'];
        });

        return array_slice($selectedAgents, 0, 3);
    }

    public function createHandoff(string $fromAgent, string $toAgent, array $context): array
    {
        $handoffId = Str::uuid()->toString();

        $this->handoffs[$handoffId] = [
            'id' => $handoffId,
            'from_agent' => $fromAgent,
            'to_agent' => $toAgent,
            'context' => $context,
            'status' => 'pending',
            'created_at' => now(),
        ];

        return $this->handoffs[$handoffId];
    }

    public function acceptHandoff(string $handoffId, string $agentId): void
    {
        if (!isset($this->handoffs[$handoffId])) {
            throw new AgentTeamException("Handoff not found: {$handoffId}");
        }

        $handoff = &$this->handoffs[$handoffId];

        if ($handoff['to_agent'] !== $agentId) {
            throw new AgentTeamException("Agent not authorized for this handoff");
        }

        $handoff['status'] = 'accepted';
        $handoff['accepted_at'] = now();
    }

    public function getWorkload(string $agentId): array
    {
        if (!isset($this->agents[$agentId])) {
            throw new AgentTeamException("Agent not found: {$agentId}");
        }

        $tasks = $this->taskQueues[$agentId] ?? [];

        return [
            'agent_id' => $agentId,
            'total_tasks' => count($tasks),
            'capacity' => $this->agents[$agentId]['capacity'],
            'utilization' => count($tasks) / $this->agents[$agentId]['capacity'],
            'in_progress' => array_filter($tasks, fn($t) => $t['status'] === 'in_progress'),
            'queued' => array_filter($tasks, fn($t) => $t['status'] === 'queued'),
        ];
    }

    public function assignTask(string $agentId, array $task, int $priority = 5): string
    {
        if (!isset($this->agents[$agentId])) {
            throw new AgentTeamException("Agent not found: {$agentId}");
        }

        $taskId = Str::uuid()->toString();

        if (!isset($this->taskQueues[$agentId])) {
            $this->taskQueues[$agentId] = [];
        }

        $this->taskQueues[$agentId][$taskId] = [
            'id' => $taskId,
            'specification' => $task,
            'priority' => $priority,
            'status' => 'queued',
            'assigned_at' => now(),
        ];

        return $taskId;
    }

    private function calculateAgentScore(array $agent, array $requirements, ?string $domain): float
    {
        $score = 0.0;

        // Check if role matches
        $requiredRole = $requirements['role'] ?? null;
        if ($requiredRole && $agent['role'] === $requiredRole) {
            $score += 50.0;
        }

        // Check specializations match
        foreach ($requirements['capabilities'] ?? [] as $capability) {
            if (in_array($capability, $agent['specializations'])) {
                $score += 25.0;
            }
        }

        // Domain preference
        if ($domain) {
            if (in_array($domain, $agent['specializations'] ?? [])) {
                $score += 15.0;
            }
        }

        // Capacity factor
        $workload = count($this->taskQueues[$agent['id']] ?? []);
        $capacityUtilization = $workload / $agent['capacity'];
        $score *= (1.0 - ($capacityUtilization * 0.3)); // Reduce score if busy

        return max(0, $score);
    }
}
