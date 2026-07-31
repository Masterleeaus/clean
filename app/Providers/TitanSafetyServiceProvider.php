<?php

namespace App\Providers;

use App\TitanOS\Safety\AuditLogs\AuditLogger;
use App\TitanOS\Safety\Contracts\AuditLogContract;
use App\TitanOS\Safety\Contracts\RateLimitContract;
use App\TitanOS\Safety\Contracts\RecoveryContract;
use App\TitanOS\Safety\Contracts\ResourceLimitContract;
use App\TitanOS\Safety\Contracts\SecurityPolicyContract;
use App\TitanOS\Safety\RateLimiting\RateLimiter;
use App\TitanOS\Safety\Recovery\RecoveryManager;
use App\TitanOS\Safety\ResourceLimits\ResourceLimitManager;
use App\TitanOS\Safety\SecurityPolicies\SecurityPolicyEnforcer;
use Illuminate\Support\ServiceProvider;

class TitanSafetyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ResourceLimitContract::class, ResourceLimitManager::class);
        $this->app->singleton(SecurityPolicyContract::class, SecurityPolicyEnforcer::class);
        $this->app->singleton(AuditLogContract::class, AuditLogger::class);
        $this->app->singleton(RateLimitContract::class, RateLimiter::class);
        $this->app->singleton(RecoveryContract::class, RecoveryManager::class);
    }

    public function boot(): void
    {
    }
}
