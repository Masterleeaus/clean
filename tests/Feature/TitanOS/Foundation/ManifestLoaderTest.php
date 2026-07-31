<?php

namespace Tests\Feature\TitanOS\Foundation;

use App\TitanOS\Foundation\AgentManifests\ManifestLoader;
use App\TitanOS\Foundation\Exceptions\InvalidManifestException;
use Tests\TestCase;

class ManifestLoaderTest extends TestCase
{
    private ManifestLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = new ManifestLoader();
    }

    /**
     * @test
     */
    public function it_loads_valid_agent_manifest_from_yaml()
    {
        $path = base_path('.titan/agents/planner.yaml');
        $manifest = $this->loader->loadAgentManifest($path);

        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('version', $manifest);
        $this->assertArrayHasKey('role', $manifest);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_missing_manifest()
    {
        $this->expectException(InvalidManifestException::class);
        $this->loader->loadAgentManifest('/nonexistent/path.yaml');
    }

    /**
     * @test
     */
    public function it_loads_capability_registry()
    {
        $path = base_path('.titan/registry/capabilities.yaml');
        $registry = $this->loader->loadCapabilityRegistry($path);

        $this->assertTrue($registry->isNotEmpty());
        $this->assertTrue($registry->has('code_analysis'));
    }

    /**
     * @test
     */
    public function it_indexes_capabilities_by_id()
    {
        $path = base_path('.titan/registry/capabilities.yaml');
        $registry = $this->loader->loadCapabilityRegistry($path);

        $capability = $registry->get('code_analysis');
        $this->assertNotNull($capability);
        $this->assertEquals('code_analysis', $capability['id']);
    }
}
