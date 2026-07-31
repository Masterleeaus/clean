<?php

namespace App\TitanOS\Execution\Contracts;

use Illuminate\Support\Collection;

interface AgentTeamContract
{
    /**
     * Register an agent with team assignment.
     *
     * @param string $agentId Unique agent identifier
     * @param string $role Agent role (planner, implementer, etc)
     * @param array $config Agent configuration
     * @return void
     */
    public function registerAgent(string $agentId, string $role, array $config = []): void;

    /**
     * Get all registered agents.
     *
     * @param string|null $role Filter by role or null for all
     * @return Collection Agents with their configuration
     */
    public function getAgents(?string $role = null): Collection;

    /**
     * Get agent details.
     *
     * @param string $agentId
     * @return array Agent information with specializations
     */
    public function getAgent(string $agentId): array;

    /**
     * Create a specialist team for a domain.
     *
     * @param string $teamName Team name
     * @param string $domain Domain/context the team specializes in
     * @param array $agents Agent IDs to include
     * @return string Team ID
     */
    public function createTeam(string $teamName, string $domain, array $agents): string;

    /**
     * Get all teams.
     *
     * @param string|null $domain Filter by domain or null for all
     * @return Collection Teams with members
     */
    public function getTeams(?string $domain = null): Collection;

    /**
     * Select best agent(s) for a task.
     *
     * @param array $taskRequirements Task capabilities needed
     * @param string|null $domain Preferred domain
     * @return array Selected agent(s) and confidence
     */
    public function selectAgents(array $taskRequirements, ?string $domain = null): array;

    /**
     * Create handoff packet for passing work between agents.
     *
     * @param string $fromAgent Source agent
     * @param string $toAgent Target agent
     * @param array $context Task context and state
     * @return array Handoff packet with metadata
     */
    public function createHandoff(string $fromAgent, string $toAgent, array $context): array;

    /**
     * Accept handoff and start work.
     *
     * @param string $handoffId Handoff packet ID
     * @param string $agentId Agent accepting work
     * @return void
     */
    public function acceptHandoff(string $handoffId, string $agentId): void;

    /**
     * Get agent's current workload.
     *
     * @param string $agentId
     * @return array Tasks in progress, capacity, queue
     */
    public function getWorkload(string $agentId): array;

    /**
     * Add task to agent queue.
     *
     * @param string $agentId
     * @param array $task Task specification
     * @param int $priority Priority level (1-10)
     * @return string Task ID
     */
    public function assignTask(string $agentId, array $task, int $priority = 5): string;
}
