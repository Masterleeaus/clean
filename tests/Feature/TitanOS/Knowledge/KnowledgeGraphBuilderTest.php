<?php

namespace Tests\Feature\TitanOS\Knowledge;

use App\TitanOS\Knowledge\KnowledgeGraph\KnowledgeGraphBuilder;
use Tests\TestCase;

class KnowledgeGraphBuilderTest extends TestCase
{
    private KnowledgeGraphBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new KnowledgeGraphBuilder();
    }

    /**
     * @test
     */
    public function it_builds_knowledge_graph_from_repository()
    {
        $basePath = app_path();
        $graphId = $this->builder->build($basePath);

        $this->assertNotEmpty($graphId);
        $this->assertTrue(str_contains($graphId, '-'));
    }

    /**
     * @test
     */
    public function it_discovers_php_files()
    {
        $basePath = app_path();
        $this->builder->build($basePath);

        $files = $this->builder->queryNodes('file');
        $this->assertGreaterThan(0, $files->count());
    }

    /**
     * @test
     */
    public function it_extracts_classes_from_files()
    {
        $basePath = app_path();
        $this->builder->build($basePath);

        $classes = $this->builder->queryNodes('class');
        $this->assertGreaterThan(0, $classes->count());
    }

    /**
     * @test
     */
    public function it_provides_graph_statistics()
    {
        $basePath = app_path();
        $this->builder->build($basePath);

        $stats = $this->builder->getStatistics();

        $this->assertArrayHasKey('total_nodes', $stats);
        $this->assertArrayHasKey('total_edges', $stats);
        $this->assertArrayHasKey('node_types', $stats);
        $this->assertGreaterThan(0, $stats['total_nodes']);
    }

    /**
     * @test
     */
    public function it_exports_to_json()
    {
        $basePath = app_path();
        $this->builder->build($basePath);

        $json = $this->builder->export('json');
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('nodes', $data);
        $this->assertArrayHasKey('edges', $data);
    }

    /**
     * @test
     */
    public function it_exports_to_graphml()
    {
        $basePath = app_path();
        $this->builder->build($basePath);

        $graphml = $this->builder->export('graphml');
        $this->assertStringContainsString('<graphml', $graphml);
        $this->assertStringContainsString('</graphml>', $graphml);
    }

    /**
     * @test
     */
    public function it_exports_to_dot()
    {
        $basePath = app_path();
        $this->builder->build($basePath);

        $dot = $this->builder->export('dot');
        $this->assertStringContainsString('digraph', $dot);
    }
}
