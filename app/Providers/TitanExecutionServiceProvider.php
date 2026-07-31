<?php

namespace App\Providers;

use App\TitanOS\Execution\AgentTeams\AgentTeamManager;
use App\TitanOS\Execution\BranchWorkflows\BranchWorkflowManager;
use App\TitanOS\Execution\Contracts\AgentTeamContract;
use App\TitanOS\Execution\Contracts\BranchWorkflowContract;
use App\TitanOS\Execution\Contracts\OwnershipLockContract;
use App\TitanOS\Execution\OwnershipLocks\OwnershipLockManager;
use Illuminate\Support\ServiceProvider;

class TitanExecutionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AgentTeamContract::class, AgentTeamManager::class);
        $this->app->singleton(OwnershipLockContract::class, OwnershipLockManager::class);
        $this->app->singleton(BranchWorkflowContract::class, function ($app) {
            return new BranchWorkflowManager(base_path());
        });
    }

    public function boot(): void
    {
    }
}
