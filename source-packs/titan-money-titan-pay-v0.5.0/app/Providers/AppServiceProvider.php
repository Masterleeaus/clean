<?php

namespace App\Providers;

use App\Console\Commands\MigrateChatAttachmentsCommand;
use App\Console\Commands\SetPlatformAdminCommand;
use App\Domains\TitanZero\WorkCore\Contracts\WorkCoreJobGateway;
use App\Domains\TitanZero\WorkCore\NativeWorkCoreJobGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WorkCoreJobGateway::class, NativeWorkCoreJobGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateChatAttachmentsCommand::class,
                SetPlatformAdminCommand::class,
            ]);
        }
    }
}
