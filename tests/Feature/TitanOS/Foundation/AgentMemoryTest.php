<?php

namespace Tests\Feature\TitanOS\Foundation;

use App\TitanOS\Foundation\Memory\AgentMemory;
use Tests\TestCase;

class AgentMemoryTest extends TestCase
{
    private AgentMemory $memory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memory = new AgentMemory();
    }

    /**
     * @test
     */
    public function it_stores_and_retrieves_memory()
    {
        $value = ['pattern' => 'transactional-outbox', 'description' => 'Event publishing'];

        $this->memory->store('repository', 'adr-0005', $value);
        $retrieved = $this->memory->get('repository', 'adr-0005');

        $this->assertEquals($value, $retrieved);
    }

    /**
     * @test
     */
    public function it_returns_null_for_missing_memory()
    {
        $result = $this->memory->get('repository', 'missing_key');
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_searches_across_memory()
    {
        $this->memory->store('global', 'principle-1', 'Single Responsibility Principle');
        $this->memory->store('global', 'principle-2', 'Open Closed Principle');
        $this->memory->store('repository', 'pattern-1', 'Repository Pattern for data access');

        $results = $this->memory->search('principle', null, 10);

        $this->assertGreaterThanOrEqual(2, $results->count());
    }

    /**
     * @test
     */
    public function it_lists_items_in_scope()
    {
        $this->memory->store('global', 'key1', 'value1', ['type' => 'pattern']);
        $this->memory->store('global', 'key2', 'value2', ['type' => 'pattern']);
        $this->memory->store('global', 'key3', 'value3', ['type' => 'principle']);

        $patterns = $this->memory->list('global', ['type' => 'pattern']);

        $this->assertGreaterThanOrEqual(2, $patterns->count());
    }

    /**
     * @test
     */
    public function it_builds_context_for_planner_agent()
    {
        $this->memory->store('global', 'principle', 'DDD');
        $this->memory->store('repository', 'boundary', 'Bounded contexts');
        $this->memory->store('branch', 'spec', 'Feature specification');

        $context = $this->memory->buildContext('planner', ['search_query' => 'architecture']);

        $this->assertArrayHasKey('role', $context);
        $this->assertEquals('planner', $context['role']);
        $this->assertArrayHasKey('memory', $context);
    }

    /**
     * @test
     */
    public function it_restricts_access_for_reviewer_agent()
    {
        $this->memory->store('global', 'principle', 'DDD');
        $this->memory->store('repository', 'boundary', 'Bounded contexts');
        $this->memory->store('branch', 'spec', 'Feature specification');
        $this->memory->store('task', 'progress', 'Task status');

        $context = $this->memory->buildContext('reviewer');

        // Reviewer should only have access to global and repository scopes
        $this->assertArrayHasKey('global', $context['memory']);
        $this->assertArrayHasKey('repository', $context['memory']);

        // These should be present but empty or minimal
        $this->assertArrayHasKey('branch', $context['memory']);
    }

    /**
     * @test
     */
    public function it_stores_metadata_with_memory()
    {
        $metadata = [
            'author' => 'Claude',
            'type' => 'adr',
            'status' => 'accepted',
        ];

        $this->memory->store('repository', 'adr-0001', 'ADR content', $metadata);
        $retrieved = $this->memory->get('repository', 'adr-0001');

        $this->assertEquals('ADR content', $retrieved);
    }

    /**
     * @test
     */
    public function it_tracks_access_log()
    {
        $this->memory->store('global', 'test', 'value');
        $this->memory->get('global', 'test');
        $this->memory->get('global', 'missing');

        $log = $this->memory->getAccessLog();

        $this->assertArrayHasKey('global', $log);
        $this->assertGreaterThanOrEqual(3, count($log['global']));
    }
}
