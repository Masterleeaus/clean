<?php

namespace App\TitanOS\Foundation\Memory;

use App\TitanOS\Foundation\Contracts\AgentMemoryContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AgentMemory implements AgentMemoryContract
{
    private const MEMORY_DISK = 'local';
    private const MEMORY_PATH = '.titan/memory';

    private array $accessLog = [];

    public function store(string $scope, string $key, mixed $value, array $metadata = []): void
    {
        $path = $this->getMemoryPath($scope, $key);

        $memory = [
            'scope' => $scope,
            'key' => $key,
            'value' => $value,
            'metadata' => $metadata,
            'stored_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        Storage::disk(self::MEMORY_DISK)->put(
            $path,
            json_encode($memory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->logAccess('store', $scope, $key);
    }

    public function get(string $scope, string $key): mixed
    {
        $path = $this->getMemoryPath($scope, $key);

        if (!Storage::disk(self::MEMORY_DISK)->exists($path)) {
            $this->logAccess('miss', $scope, $key);
            return null;
        }

        $content = Storage::disk(self::MEMORY_DISK)->get($path);
        $memory = json_decode($content, true);

        $this->logAccess('get', $scope, $key);

        return $memory['value'] ?? null;
    }

    public function search(string $query, ?string $scope = null, int $limit = 10): Collection
    {
        $searchPath = self::MEMORY_PATH . ($scope ? "/{$scope}" : '');

        if (!Storage::disk(self::MEMORY_DISK)->exists($searchPath)) {
            return collect();
        }

        $files = Storage::disk(self::MEMORY_DISK)->files($searchPath);
        $results = [];

        foreach ($files as $file) {
            if (!str_ends_with($file, '.json')) {
                continue;
            }

            $content = Storage::disk(self::MEMORY_DISK)->get($file);
            $memory = json_decode($content, true);

            if ($this->matchesQuery($memory, $query)) {
                $results[] = $memory;

                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        return collect($results);
    }

    public function list(string $scope, array $filter = []): Collection
    {
        $path = self::MEMORY_PATH . "/{$scope}";

        if (!Storage::disk(self::MEMORY_DISK)->exists($path)) {
            return collect();
        }

        $files = Storage::disk(self::MEMORY_DISK)->files($path);
        $memories = [];

        foreach ($files as $file) {
            if (!str_ends_with($file, '.json')) {
                continue;
            }

            $content = Storage::disk(self::MEMORY_DISK)->get($file);
            $memory = json_decode($content, true);

            if ($this->passesFilters($memory, $filter)) {
                $memories[] = $memory;
            }
        }

        return collect($memories);
    }

    public function buildContext(string $agentRole, array $options = []): array
    {
        $context = [
            'role' => $agentRole,
            'timestamp' => now()->toIso8601String(),
            'memory' => [],
        ];

        $accessScopes = $this->getAgentAccessScopes($agentRole);

        foreach ($accessScopes as $scope) {
            $context['memory'][$scope] = $this->list($scope)->toArray();
        }

        if (!empty($options['search_query'])) {
            $context['search_results'] = $this->search(
                $options['search_query'],
                null,
                $options['search_limit'] ?? 10
            )->toArray();
        }

        return $context;
    }

    public function prune(string $scope, int $retentionDays): int
    {
        $path = self::MEMORY_PATH . "/{$scope}";

        if (!Storage::disk(self::MEMORY_DISK)->exists($path)) {
            return 0;
        }

        $files = Storage::disk(self::MEMORY_DISK)->files($path);
        $deleted = 0;
        $cutoffDate = now()->subDays($retentionDays);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.json')) {
                continue;
            }

            $content = Storage::disk(self::MEMORY_DISK)->get($file);
            $memory = json_decode($content, true);

            if (isset($memory['stored_at'])) {
                $storedAt = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $memory['stored_at']);

                if ($storedAt && $storedAt < $cutoffDate) {
                    Storage::disk(self::MEMORY_DISK)->delete($file);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    private function getMemoryPath(string $scope, string $key): string
    {
        return self::MEMORY_PATH . "/{$scope}/" . $this->sanitizeKey($key) . '.json';
    }

    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-z0-9_\-]/i', '_', $key);
    }

    private function matchesQuery(array $memory, string $query): bool
    {
        $queryLower = strtolower($query);

        return str_contains(strtolower($memory['key'] ?? ''), $queryLower) ||
               str_contains(strtolower(json_encode($memory['value'] ?? '')), $queryLower) ||
               str_contains(strtolower(json_encode($memory['metadata'] ?? [])), $queryLower);
    }

    private function passesFilters(array $memory, array $filter): bool
    {
        if (isset($filter['key_pattern'])) {
            if (!preg_match($filter['key_pattern'], $memory['key'] ?? '')) {
                return false;
            }
        }

        if (isset($filter['type'])) {
            if (!isset($memory['metadata']['type']) || $memory['metadata']['type'] !== $filter['type']) {
                return false;
            }
        }

        return true;
    }

    private function getAgentAccessScopes(string $agentRole): array
    {
        $scopes = [self::SCOPE_GLOBAL];

        $roleScopes = [
            'planner' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY, self::SCOPE_BRANCH, self::SCOPE_TASK, 'agent'],
            'implementer' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY, self::SCOPE_BRANCH, self::SCOPE_TASK, 'agent'],
            'reviewer' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY],
            'tester' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY, self::SCOPE_BRANCH, self::SCOPE_TASK],
            'security_agent' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY, self::SCOPE_BRANCH, self::SCOPE_TASK],
            'documentation_agent' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY, self::SCOPE_BRANCH, self::SCOPE_TASK],
            'release_agent' => [self::SCOPE_GLOBAL, self::SCOPE_REPOSITORY, self::SCOPE_TASK],
        ];

        return $roleScopes[$agentRole] ?? $scopes;
    }

    private function logAccess(string $action, string $scope, string $key): void
    {
        if (!isset($this->accessLog[$scope])) {
            $this->accessLog[$scope] = [];
        }

        $this->accessLog[$scope][] = [
            'action' => $action,
            'key' => $key,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function getAccessLog(): array
    {
        return $this->accessLog;
    }
}
