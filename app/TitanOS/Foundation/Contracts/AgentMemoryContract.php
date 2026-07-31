<?php

namespace App\TitanOS\Foundation\Contracts;

use Illuminate\Support\Collection;

interface AgentMemoryContract
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_REPOSITORY = 'repository';
    const SCOPE_BRANCH = 'branch';
    const SCOPE_TASK = 'task';
    const SCOPE_AGENT = 'agent';

    /**
     * Store memory item in specific scope.
     *
     * @param string $scope One of SCOPE_* constants
     * @param string $key Memory key
     * @param mixed $value Memory value
     * @param array $metadata Optional metadata
     * @return void
     */
    public function store(string $scope, string $key, mixed $value, array $metadata = []): void;

    /**
     * Retrieve memory item.
     *
     * @param string $scope
     * @param string $key
     * @return mixed|null
     */
    public function get(string $scope, string $key): mixed;

    /**
     * Search memory across scopes.
     *
     * @param string $query Search query
     * @param string|null $scope Limit to scope or search all
     * @param int $limit
     * @return Collection Search results
     */
    public function search(string $query, ?string $scope = null, int $limit = 10): Collection;

    /**
     * List memory items in scope.
     *
     * @param string $scope
     * @param array $filter Optional filters
     * @return Collection
     */
    public function list(string $scope, array $filter = []): Collection;

    /**
     * Build context for agent with scoped memory.
     *
     * @param string $agentRole Agent role/type
     * @param array $options Context options
     * @return array Context data for agent
     */
    public function buildContext(string $agentRole, array $options = []): array;

    /**
     * Delete old memory items (archival).
     *
     * @param string $scope
     * @param int $retentionDays How old to delete
     * @return int Number deleted
     */
    public function prune(string $scope, int $retentionDays): int;
}
