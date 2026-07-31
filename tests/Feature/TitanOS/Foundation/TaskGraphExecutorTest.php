<?php

namespace Tests\Feature\TitanOS\Foundation;

use App\TitanOS\Foundation\TaskGraphs\TaskGraphExecutor;
use App\TitanOS\Foundation\Exceptions\InvalidTaskGraphException;
use Tests\TestCase;

class TaskGraphExecutorTest extends TestCase
{
    private TaskGraphExecutor $executor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executor = new TaskGraphExecutor();
    }

    /**
     * @test
     */
    public function it_validates_task_graph_structure()
    {
        $validGraph = [
            'name' => 'Test Plan',
            'tasks' => [
                'task1' => ['action' => 'do_something'],
                'task2' => ['action' => 'do_another', 'depends_on' => ['task1']],
            ],
        ];

        $this->assertTrue($this->executor->validateGraph($validGraph));
    }

    /**
     * @test
     */
    public function it_rejects_graph_without_tasks()
    {
        $this->expectException(InvalidTaskGraphException::class);
        $this->executor->validateGraph(['name' => 'Invalid Plan']);
    }

    /**
     * @test
     */
    public function it_detects_missing_dependencies()
    {
        $this->expectException(InvalidTaskGraphException::class);

        $invalidGraph = [
            'tasks' => [
                'task1' => ['action' => 'do_something'],
                'task2' => ['action' => 'do_another', 'depends_on' => ['missing_task']],
            ],
        ];

        $this->executor->validateGraph($invalidGraph);
    }

    /**
     * @test
     */
    public function it_executes_simple_task_graph()
    {
        $graphPath = $this->createTempTaskGraph([
            'name' => 'Simple Plan',
            'tasks' => [
                'task1' => ['action' => 'test_action'],
                'task2' => ['action' => 'another_action', 'depends_on' => ['task1']],
            ],
        ]);

        $result = $this->executor->execute($graphPath, ['test' => 'data']);

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('execution_id', $result);
        $this->assertArrayHasKey('tasks', $result);
    }

    private function createTempTaskGraph(array $graph): string
    {
        $path = storage_path('temp_plan_' . uniqid() . '.yaml');
        $yaml = \Symfony\Component\Yaml\Yaml::dump($graph);
        file_put_contents($path, $yaml);
        return $path;
    }
}
