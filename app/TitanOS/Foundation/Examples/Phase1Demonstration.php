<?php

namespace App\TitanOS\Foundation\Examples;

use App\TitanOS\Foundation\AgentManifests\ManifestLoader;
use App\TitanOS\Foundation\Contracts\{
    AgentMemoryContract,
    DurableExecutionContract,
    ManifestLoaderContract,
    TaskGraphExecutorContract,
};
use Symfony\Component\Yaml\Yaml;

/**
 * Phase 1 Foundation Demonstration
 *
 * Shows how all Phase 1 components work together:
 * 1. Load agent manifests and capabilities
 * 2. Execute a task graph with checkpoints
 * 3. Store execution state durably
 * 4. Access memory for context
 *
 * Usage:
 *   $demo = new Phase1Demonstration();
 *   $demo->run();
 */
class Phase1Demonstration
{
    private ManifestLoaderContract $manifests;
    private TaskGraphExecutorContract $executor;
    private DurableExecutionContract $durable;
    private AgentMemoryContract $memory;

    public function __construct(
        ManifestLoaderContract $manifests,
        TaskGraphExecutorContract $executor,
        DurableExecutionContract $durable,
        AgentMemoryContract $memory,
    ) {
        $this->manifests = $manifests;
        $this->executor = $executor;
        $this->durable = $durable;
        $this->memory = $memory;
    }

    public function run(): void
    {
        echo "=== Titan Phase 1 Foundation Demonstration ===\n\n";

        // Step 1: Load Agent Manifests
        echo "Step 1: Loading Agent Manifests\n";
        $this->demoManifests();

        // Step 2: Execute Task Graph
        echo "\nStep 2: Executing Task Graph with Checkpoints\n";
        $executionId = $this->demoTaskExecution();

        // Step 3: Durable Execution
        echo "\nStep 3: Durable Execution with State Persistence\n";
        $this->demoDurableExecution($executionId);

        // Step 4: Agent Memory
        echo "\nStep 4: Agent Memory System\n";
        $this->demoMemory();

        echo "\n=== Demonstration Complete ===\n";
    }

    private function demoManifests(): void
    {
        $manifestPath = base_path('.titan/agents/planner.yaml');
        $registryPath = base_path('.titan/registry/capabilities.yaml');

        try {
            // Load planner manifest
            $manifest = $this->manifests->loadAgentManifest($manifestPath);
            echo "✓ Loaded agent manifest: {$manifest['name']}\n";
            echo "  Role: {$manifest['role']}\n";
            echo "  Capabilities: " . count($manifest['capabilities']) . "\n";

            // Load capability registry
            $capabilities = $this->manifests->loadCapabilityRegistry($registryPath);
            echo "✓ Loaded capability registry: " . $capabilities->count() . " capabilities\n";
            echo "  Providers:\n";

            $capabilities->take(3)->each(function ($cap) {
                $providers = collect($cap['providers'] ?? [])->pluck('id')->join(', ');
                echo "    - {$cap['id']}: [$providers]\n";
            });
        } catch (\Exception $e) {
            echo "✗ Error: {$e->getMessage()}\n";
        }
    }

    private function demoTaskExecution(): string
    {
        try {
            // Create a simple task graph
            $graph = [
                'name' => 'Demo Workflow',
                'description' => 'Phase 1 demonstration task graph',
                'metadata' => ['demo' => true],
                'tasks' => [
                    'analyze_code' => [
                        'action' => 'code:analyze',
                        'description' => 'Analyze code quality',
                    ],
                    'run_tests' => [
                        'action' => 'test:run',
                        'description' => 'Run test suite',
                        'depends_on' => ['analyze_code'],
                    ],
                    'generate_report' => [
                        'action' => 'report:generate',
                        'description' => 'Generate quality report',
                        'depends_on' => ['run_tests'],
                    ],
                ],
            ];

            // Validate graph
            $this->executor->validateGraph($graph);
            echo "✓ Task graph validated\n";
            echo "  Tasks: " . count($graph['tasks']) . "\n";

            // Execute graph
            $result = $this->executor->execute(
                $this->createTempGraph($graph),
                ['branch' => 'main', 'user' => 'claude']
            );

            echo "✓ Task graph executed\n";
            echo "  Execution ID: {$result['execution_id']}\n";
            echo "  Status: {$result['status']}\n";
            echo "  Tasks completed: " . count($result['tasks']) . "\n";

            // Get status
            $status = $this->executor->getStatus($result['execution_id']);
            echo "  Checkpoints: " . count($status['checkpoints']) . "\n";

            return $result['execution_id'];
        } catch (\Exception $e) {
            echo "✗ Error: {$e->getMessage()}\n";
            return '';
        }
    }

    private function demoDurableExecution(string $executionId): void
    {
        try {
            // Simulate state at different stages
            $stage1 = ['step' => 'analysis', 'files_checked' => 127, 'issues_found' => 15];
            $stage2 = ['step' => 'testing', 'tests_run' => 450, 'failures' => 3];
            $stage3 = ['step' => 'complete', 'quality_score' => 87.5, 'ready_for_merge' => true];

            // Create checkpoints at each stage
            $cp1 = $this->durable->createCheckpoint($executionId, $stage1, [
                'test_results' => 'passed',
                'time_ms' => 2400,
            ]);
            echo "✓ Checkpoint 1 created: {$cp1}\n";
            echo "  State: {$stage1['step']}\n";

            $cp2 = $this->durable->createCheckpoint($executionId, $stage2, [
                'test_results' => 'mostly_passed',
                'time_ms' => 4100,
            ]);
            echo "✓ Checkpoint 2 created: {$cp2}\n";
            echo "  State: {$stage2['step']}\n";

            $cp3 = $this->durable->createCheckpoint($executionId, $stage3, [
                'test_results' => 'all_passed',
                'time_ms' => 500,
            ]);
            echo "✓ Checkpoint 3 created: {$cp3}\n";
            echo "  State: {$stage3['step']}\n";

            // Verify checkpoints
            echo "\n✓ Checkpoint verification:\n";
            echo "  CP1 valid: " . ($this->durable->verifyCheckpoint($cp1) ? 'yes' : 'no') . "\n";
            echo "  CP2 valid: " . ($this->durable->verifyCheckpoint($cp2) ? 'yes' : 'no') . "\n";
            echo "  CP3 valid: " . ($this->durable->verifyCheckpoint($cp3) ? 'yes' : 'no') . "\n";

            // Get execution trace
            $trace = $this->durable->getExecutionTrace($executionId);
            echo "\n✓ Execution trace:\n";
            echo "  Execution ID: {$trace['execution_id']}\n";
            echo "  Total checkpoints: " . count($trace['checkpoints']) . "\n";
            echo "  Updated: {$trace['updated_at']}\n";

            // Restore from checkpoint
            $restored = $this->durable->restoreFromCheckpoint($cp2);
            echo "\n✓ Restored state from CP2:\n";
            echo "  Step: {$restored['step']}\n";
            echo "  Tests run: {$restored['tests_run']}\n";
        } catch (\Exception $e) {
            echo "✗ Error: {$e->getMessage()}\n";
        }
    }

    private function demoMemory(): void
    {
        try {
            // Store different types of memory
            echo "✓ Storing memory:\n";

            $this->memory->store('global', 'principle-ddd', 'Domain Driven Design', [
                'type' => 'principle',
                'priority' => 'high',
            ]);
            echo "  Stored: principle-ddd\n";

            $this->memory->store('repository', 'adr-0001', 'Transactional Outbox Pattern', [
                'type' => 'adr',
                'status' => 'accepted',
                'date' => '2026-07-30',
            ]);
            echo "  Stored: adr-0001\n";

            $this->memory->store('branch', 'feature-spec', 'Payment processing refactor', [
                'type' => 'specification',
                'complexity' => 'high',
            ]);
            echo "  Stored: feature-spec\n";

            // Search memory
            echo "\n✓ Searching memory:\n";
            $results = $this->memory->search('pattern', null, 10);
            echo "  Found " . $results->count() . " results for 'pattern'\n";

            // Build context for different agents
            echo "\n✓ Building context for agents:\n";

            $plannerContext = $this->memory->buildContext('planner', [
                'search_query' => 'domain driven',
                'search_limit' => 3,
            ]);
            echo "  Planner context:\n";
            echo "    Scopes: " . implode(', ', array_keys($plannerContext['memory'])) . "\n";
            echo "    Memory items: " . collect($plannerContext['memory'])->sum(fn($items) => count($items)) . "\n";

            $reviewerContext = $this->memory->buildContext('reviewer');
            echo "  Reviewer context:\n";
            echo "    Scopes: " . implode(', ', array_keys($reviewerContext['memory'])) . "\n";

            // Access log
            $log = $this->memory->getAccessLog();
            echo "\n✓ Memory access log:\n";
            echo "  Total accesses: " . collect($log)->sum(fn($accesses) => count($accesses)) . "\n";
            echo "  Scopes accessed: " . count($log) . "\n";
        } catch (\Exception $e) {
            echo "✗ Error: {$e->getMessage()}\n";
        }
    }

    private function createTempGraph(array $graph): string
    {
        $path = storage_path('demo_graph_' . uniqid() . '.yaml');
        $yaml = Yaml::dump($graph);
        file_put_contents($path, $yaml);
        return $path;
    }
}
