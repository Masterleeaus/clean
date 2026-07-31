<?php

namespace App\Console\Commands;

use App\TitanOS\Foundation\Examples\Phase1Demonstration;
use App\TitanOS\Foundation\Contracts\{
    AgentMemoryContract,
    DurableExecutionContract,
    ManifestLoaderContract,
    TaskGraphExecutorContract,
};
use Illuminate\Console\Command;

class TitanFoundationDemoCommand extends Command
{
    protected $signature = 'titan:demo {--component=all : Component to demo (all, manifests, executor, durable, memory)}';
    protected $description = 'Run Titan Phase 1 Foundation demonstration';

    public function handle(): int
    {
        $component = $this->option('component');

        $demo = new Phase1Demonstration(
            app(ManifestLoaderContract::class),
            app(TaskGraphExecutorContract::class),
            app(DurableExecutionContract::class),
            app(AgentMemoryContract::class),
        );

        if ($component === 'all') {
            $demo->run();
        } else {
            $this->error("Component demo not yet implemented: {$component}");
            return 1;
        }

        return 0;
    }
}
