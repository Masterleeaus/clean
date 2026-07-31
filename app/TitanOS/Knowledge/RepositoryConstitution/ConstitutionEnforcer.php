<?php

namespace App\TitanOS\Knowledge\RepositoryConstitution;

use App\TitanOS\Knowledge\Contracts\RepositoryConstitutionContract;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class ConstitutionEnforcer implements RepositoryConstitutionContract
{
    private array $contexts = [];
    private array $ownership = [];
    private array $boundaries = [];
    private array $violations = [];

    public function load(string $configPath): array
    {
        if (!file_exists($configPath)) {
            return [];
        }

        $config = Yaml::parseFile($configPath);

        if (isset($config['bounded_contexts'])) {
            foreach ($config['bounded_contexts'] as $name => $context) {
                $this->defineContext($name, $context);
            }
        }

        if (isset($config['ownership'])) {
            foreach ($config['ownership'] as $pattern => $owner) {
                $this->setOwnership($pattern, $owner);
            }
        }

        if (isset($config['boundaries'])) {
            foreach ($config['boundaries'] as $name => $boundary) {
                $this->defineBoundary($name, $boundary);
            }
        }

        return $config;
    }

    public function defineContext(string $name, array $config): void
    {
        $this->contexts[$name] = [
            'name' => $name,
            'path_pattern' => $config['path_pattern'] ?? "app/{$name}/**",
            'dependencies' => $config['dependencies'] ?? [],
            'metadata' => $config['metadata'] ?? [],
        ];
    }

    public function getContexts(): Collection
    {
        return collect($this->contexts);
    }

    public function setOwnership(string $pattern, string $owner, array $metadata = []): void
    {
        $this->ownership[$pattern] = [
            'owner' => $owner,
            'metadata' => $metadata,
        ];
    }

    public function getFileOwner(string $filePath): ?string
    {
        foreach ($this->ownership as $pattern => $info) {
            if ($this->matchesPattern($filePath, $pattern)) {
                return $info['owner'];
            }
        }

        return null;
    }

    public function defineBoundary(string $name, array $config): void
    {
        $this->boundaries[$name] = [
            'name' => $name,
            'path_pattern' => $config['path_pattern'],
            'context' => $config['context'] ?? null,
            'restrictions' => $config['restrictions'] ?? [],
        ];
    }

    public function validate(string $filePath): array
    {
        $violations = [];

        // Check ownership
        $owner = $this->getFileOwner($filePath);
        if (!$owner) {
            $violations[] = [
                'type' => 'unowned',
                'file' => $filePath,
                'message' => "File has no assigned owner",
            ];
        }

        // Check boundary violations
        foreach ($this->boundaries as $boundary) {
            if ($this->matchesPattern($filePath, $boundary['path_pattern'])) {
                $boundaryViolations = $this->checkBoundary($filePath, $boundary);
                $violations = array_merge($violations, $boundaryViolations);
            }
        }

        return $violations;
    }

    public function getBoundaryViolations(): Collection
    {
        $violations = [];

        // This would scan the actual codebase for violations
        // Simplified implementation

        return collect($violations);
    }

    public function getOwnershipViolations(): Collection
    {
        $violations = [];

        // This would check git history for modifications
        // Simplified implementation

        return collect($violations);
    }

    private function checkBoundary(string $filePath, array $boundary): array
    {
        $violations = [];

        if (!isset($boundary['restrictions'])) {
            return $violations;
        }

        foreach ($boundary['restrictions'] as $restriction) {
            // Check if file violates restrictions
            if ($restriction['type'] === 'no_import' && $this->importsRestricted($filePath, $restriction['from'])) {
                $violations[] = [
                    'type' => 'boundary_violation',
                    'file' => $filePath,
                    'boundary' => $boundary['name'],
                    'message' => "Cannot import from {$restriction['from']}",
                ];
            }
        }

        return $violations;
    }

    private function matchesPattern(string $path, string $pattern): bool
    {
        // Convert glob pattern to regex
        $regex = str_replace(
            ['*', '?', '/'],
            ['.*', '.', '\/'],
            $pattern
        );

        return preg_match("/{$regex}/", $path) === 1;
    }

    private function importsRestricted(string $filePath, string $restrictedImport): bool
    {
        $content = @file_get_contents($filePath);
        if (!$content) {
            return false;
        }

        return str_contains($content, "use {$restrictedImport}") ||
               str_contains($content, "from '{$restrictedImport}'");
    }
}
