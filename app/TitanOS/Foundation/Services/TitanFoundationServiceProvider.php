<?php

namespace App\TitanOS\Foundation\Services;

use App\TitanOS\Foundation\AgentManifests\ManifestLoader;
use App\TitanOS\Foundation\Contracts\{
    AgentMemoryContract,
    DurableExecutionContract,
    ManifestLoaderContract,
    TaskGraphExecutorContract,
};
use App\TitanOS\Foundation\DurableExecution\DurableExecutor;
use App\TitanOS\Foundation\Memory\AgentMemory;
use App\TitanOS\Foundation\TaskGraphs\TaskGraphExecutor;
use Illuminate\Support\ServiceProvider;

class TitanFoundationServiceProvider extends ServiceProvider
{
    /**
     * Register Titan Foundation services.
     */
    public function register(): void
    {
        // Phase 1.1: Agent Manifests & Capability Registry
        $this->app->singleton(ManifestLoaderContract::class, ManifestLoader::class);

        // Phase 1.2: Typed Task Graphs
        $this->app->singleton(TaskGraphExecutorContract::class, TaskGraphExecutor::class);

        // Phase 1.3: Durable Execution Engine
        $this->app->singleton(DurableExecutionContract::class, DurableExecutor::class);

        // Phase 1.4: Agent Memory System
        $this->app->singleton(AgentMemoryContract::class, AgentMemory::class);
    }

    /**
     * Bootstrap Titan Foundation services.
     */
    public function boot(): void
    {
        //
    }
}
