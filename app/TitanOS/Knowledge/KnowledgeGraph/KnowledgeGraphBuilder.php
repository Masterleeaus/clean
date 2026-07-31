<?php

namespace App\TitanOS\Knowledge\KnowledgeGraph;

use App\TitanOS\Knowledge\Contracts\KnowledgeGraphContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class KnowledgeGraphBuilder implements KnowledgeGraphContract
{
    private array $nodes = [];
    private array $edges = [];
    private string $graphId = '';

    public function build(string $basePath, array $options = []): string
    {
        $this->graphId = Str::uuid()->toString();
        $this->nodes = [];
        $this->edges = [];

        // Discover PHP files
        $this->discoverPhpFiles($basePath);

        // Analyze dependencies
        $this->analyzeDependencies($basePath);

        // Discover routes
        if ($options['include_routes'] ?? true) {
            $this->discoverRoutes($basePath);
        }

        return $this->graphId;
    }

    public function queryNodes(string $type, array $filters = []): Collection
    {
        $nodes = collect($this->nodes)->filter(fn($node) => $node['type'] === $type);

        foreach ($filters as $key => $value) {
            $nodes = $nodes->filter(fn($node) => ($node['metadata'][$key] ?? null) === $value);
        }

        return $nodes;
    }

    public function queryEdges(string $from, ?string $to = null): Collection
    {
        $edges = collect($this->edges)->filter(fn($edge) => $edge['from'] === $from);

        if ($to) {
            $edges = $edges->filter(fn($edge) => $edge['to'] === $to);
        }

        return $edges;
    }

    public function findDependencyPath(string $from, string $to): array
    {
        $visited = [];
        $path = $this->dfs($from, $to, $visited);
        return $path ?? [];
    }

    public function findCycles(): Collection
    {
        $cycles = [];
        $visited = [];
        $recursionStack = [];

        foreach ($this->nodes as $nodeId => $node) {
            if (!isset($visited[$nodeId])) {
                $this->detectCycles($nodeId, $visited, $recursionStack, $cycles);
            }
        }

        return collect($cycles);
    }

    public function getNodeDetails(string $nodeId): array
    {
        $node = $this->nodes[$nodeId] ?? null;

        if (!$node) {
            return [];
        }

        $inbound = collect($this->edges)->filter(fn($e) => $e['to'] === $nodeId);
        $outbound = collect($this->edges)->filter(fn($e) => $e['from'] === $nodeId);

        return [
            'node' => $node,
            'inbound' => $inbound->toArray(),
            'outbound' => $outbound->toArray(),
        ];
    }

    public function export(string $format = 'json'): string
    {
        return match ($format) {
            'json' => json_encode([
                'id' => $this->graphId,
                'nodes' => $this->nodes,
                'edges' => $this->edges,
            ], JSON_PRETTY_PRINT),
            'graphml' => $this->exportGraphML(),
            'dot' => $this->exportDot(),
            default => json_encode(['error' => 'Unknown format']),
        };
    }

    public function getStatistics(): array
    {
        $nodeTypes = collect($this->nodes)->groupBy('type')->map->count();
        $edgeTypes = collect($this->edges)->groupBy('type')->map->count();

        return [
            'graph_id' => $this->graphId,
            'total_nodes' => count($this->nodes),
            'total_edges' => count($this->edges),
            'node_types' => $nodeTypes->toArray(),
            'edge_types' => $edgeTypes->toArray(),
            'cycles_found' => $this->findCycles()->count(),
        ];
    }

    private function discoverPhpFiles(string $basePath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
                continue;
            }

            if ($this->shouldSkipFile($fileInfo->getPathname())) {
                continue;
            }

            $this->analyzePhpFile($fileInfo);
        }
    }

    private function analyzePhpFile(SplFileInfo $fileInfo): void
    {
        $content = file_get_contents($fileInfo->getRealPath());
        $relativePath = str_replace(getcwd() . '/', '', $fileInfo->getRealPath());

        $nodeId = $this->createNodeId('file', $relativePath);

        $this->nodes[$nodeId] = [
            'id' => $nodeId,
            'type' => 'file',
            'name' => $fileInfo->getFilename(),
            'path' => $relativePath,
            'metadata' => [
                'size' => $fileInfo->getSize(),
                'lines' => substr_count($content, "\n"),
            ],
        ];

        $this->extractClasses($content, $relativePath);
        $this->extractFunctions($content, $relativePath);
    }

    private function extractClasses(string $content, string $filePath): void
    {
        if (!preg_match_all('/class\s+(\w+)/', $content, $matches)) {
            return;
        }

        foreach ($matches[1] as $className) {
            $nodeId = $this->createNodeId('class', $className);

            $this->nodes[$nodeId] = [
                'id' => $nodeId,
                'type' => 'class',
                'name' => $className,
                'file' => $filePath,
                'metadata' => [],
            ];

            // Create edge from file to class
            $this->edges[] = [
                'from' => $this->createNodeId('file', $filePath),
                'to' => $nodeId,
                'type' => 'contains',
            ];
        }
    }

    private function extractFunctions(string $content, string $filePath): void
    {
        if (!preg_match_all('/function\s+(\w+)/', $content, $matches)) {
            return;
        }

        foreach ($matches[1] as $funcName) {
            $nodeId = $this->createNodeId('function', $funcName . '@' . $filePath);

            $this->nodes[$nodeId] = [
                'id' => $nodeId,
                'type' => 'function',
                'name' => $funcName,
                'file' => $filePath,
                'metadata' => [],
            ];
        }
    }

    private function analyzeDependencies(string $basePath): void
    {
        // This is a simplified implementation
        // Real implementation would use PHP-Parser for accurate AST analysis
    }

    private function discoverRoutes(string $basePath): void
    {
        // This would discover Laravel routes
        // Simplified for now
    }

    private function dfs(string $current, string $target, array &$visited, array $path = []): ?array
    {
        $visited[$current] = true;
        $path[] = $current;

        if ($current === $target) {
            return $path;
        }

        $edges = collect($this->edges)->filter(fn($e) => $e['from'] === $current);

        foreach ($edges as $edge) {
            if (!isset($visited[$edge['to']])) {
                $result = $this->dfs($edge['to'], $target, $visited, $path);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function detectCycles(string $nodeId, array &$visited, array &$recursionStack, array &$cycles): void
    {
        $visited[$nodeId] = true;
        $recursionStack[$nodeId] = true;

        $edges = collect($this->edges)->filter(fn($e) => $e['from'] === $nodeId);

        foreach ($edges as $edge) {
            if (!isset($visited[$edge['to']])) {
                $this->detectCycles($edge['to'], $visited, $recursionStack, $cycles);
            } elseif ($recursionStack[$edge['to']] ?? false) {
                $cycles[] = [$nodeId, $edge['to']];
            }
        }

        unset($recursionStack[$nodeId]);
    }

    private function shouldSkipFile(string $path): bool
    {
        $skipPatterns = ['/vendor/', '/node_modules/', '/.git/', '/storage/', '/bootstrap/cache/'];
        foreach ($skipPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function createNodeId(string $type, string $name): string
    {
        return "{$type}:" . Str::slug($name);
    }

    private function exportGraphML(): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<graphml xmlns=\"http://graphml.graphdrawing.org/xmlns\">\n";
        $xml .= "  <graph id=\"{$this->graphId}\" edgedefault=\"directed\">\n";

        foreach ($this->nodes as $node) {
            $xml .= "    <node id=\"{$node['id']}\" label=\"{$node['name']}\"/>\n";
        }

        foreach ($this->edges as $edge) {
            $xml .= "    <edge source=\"{$edge['from']}\" target=\"{$edge['to']}\"/>\n";
        }

        $xml .= "  </graph>\n";
        $xml .= "</graphml>";

        return $xml;
    }

    private function exportDot(): string
    {
        $dot = "digraph G {\n";

        foreach ($this->nodes as $node) {
            $dot .= "  \"{$node['id']}\" [label=\"{$node['name']}\"];\n";
        }

        foreach ($this->edges as $edge) {
            $dot .= "  \"{$edge['from']}\" -> \"{$edge['to']}\";\n";
        }

        $dot .= "}\n";

        return $dot;
    }
}
