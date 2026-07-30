<?php

declare(strict_types=1);

namespace App\Providers;

use App\Titan\Audit\AuditRecorder;
use App\Titan\Audit\DatabaseAuditRecorder;
use App\Services\AIAssistantService;
use App\Titan\AI\ConversationContextBuilder;
use App\Titan\AI\TitanZeroOrchestrator;
use App\Titan\AI\ToolRouter;
use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Extensions\ExtensionManager;
use App\Titan\Extensions\ExtensionRegistry;
use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\CompanyContextResolver;
use App\Titan\Vault\DatabaseVault;
use App\Titan\Vault\Vault;
use Illuminate\Support\ServiceProvider;

final class TitanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('titan.php'), 'titan');

        $this->app->scoped(CompanyContextResolver::class);
        $this->app->scoped(PermissionResolver::class);
        $this->app->scoped(AIAssistantService::class);
        $this->app->scoped(ConversationContextBuilder::class);
        $this->app->scoped(ToolRouter::class);
        $this->app->scoped(TitanZeroOrchestrator::class);
        $this->app->singleton(Vault::class, DatabaseVault::class);
        $this->app->singleton(AuditRecorder::class, DatabaseAuditRecorder::class);
        $this->app->singleton(CapabilityRegistry::class);
        $this->app->singleton(ExtensionRegistry::class, fn () => new ExtensionRegistry(
            (string) config('titan.extensions.path', app_path('Extensions')),
        ));
        $this->app->singleton(ExtensionManager::class);
    }

    public function boot(CapabilityRegistry $capabilities): void
    {
        $this->loadMigrationsFrom(database_path('migrations/extensions/titan_maps'));

        foreach ((array) config('titan.host_capabilities', []) as $capability) {
            $capabilities->register([
                'id' => 'host.' . $capability,
                'provider' => 'meetup-titan-host',
                'version' => '0.1.0',
                'kind' => 'host',
            ]);
        }

        $this->app->booted(function (): void {
            $this->app->make(ExtensionManager::class)->bootEnabledExtensions();
        });
    }
}
