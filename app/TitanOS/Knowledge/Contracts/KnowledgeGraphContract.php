<?php

namespace App\TitanOS\Knowledge\Contracts;

use Illuminate\Support\Collection;

interface KnowledgeGraphContract
{
    /**
     * Build knowledge graph from repository.
     *
     * @param string $basePath Repository root path
     * @param array $options Configuration options
     * @return string Graph ID
     */
    public function build(string $basePath, array $options = []): string;

    /**
     * Query nodes in the graph.
     *
     * @param string $type Node type (file, class, function, route, etc)
     * @param array $filters Query filters
     * @return Collection Query results
     */
    public function queryNodes(string $type, array $filters = []): Collection;

    /**
     * Query edges (dependencies) between nodes.
     *
     * @param string $from Source node ID
     * @param string|null $to Target node ID or null for all
     * @return Collection Edges with relationship metadata
     */
    public function queryEdges(string $from, ?string $to = null): Collection;

    /**
     * Find dependency path between two nodes.
     *
     * @param string $from Source node ID
     * @param string $to Target node ID
     * @return array Path steps with intermediate nodes
     */
    public function findDependencyPath(string $from, string $to): array;

    /**
     * Find circular dependencies in graph.
     *
     * @return Collection Array of cycles with involved nodes
     */
    public function findCycles(): Collection;

    /**
     * Get node details with all connections.
     *
     * @param string $nodeId
     * @return array Node with incoming/outgoing edges
     */
    public function getNodeDetails(string $nodeId): array;

    /**
     * Export graph for visualization.
     *
     * @param string $format json|graphml|dot
     * @return string Serialized graph
     */
    public function export(string $format = 'json'): string;

    /**
     * Get graph statistics.
     *
     * @return array Stats with node counts, edge counts, metrics
     */
    public function getStatistics(): array;
}
