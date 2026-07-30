<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Infrastructure\Providers;

use App\Domains\TitanTrain\Domain\Blueprints\CleanerFoundationBlueprint;
use App\Domains\TitanTrain\Application\Actions\AssignCleaner;
use App\Domains\TitanTrain\Application\Actions\CompleteLesson;
use App\Domains\TitanTrain\Application\Actions\GetReadiness;
use App\Domains\TitanTrain\Application\Actions\ListAssignments;
use App\Domains\TitanTrain\Infrastructure\Console\SetupCleanerTrainingCommand;
use App\Domains\WorkCore\System\Actions\ActionDefinition;
use App\Domains\WorkCore\System\Actions\BusinessActionRegistry;
use App\Domains\WorkCore\System\Capabilities\CapabilityDefinition;
use App\Domains\WorkCore\System\Capabilities\CapabilityRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class TitanTrainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../Config/titan_train.php', 'titan_train');
        $this->app->singleton(CleanerFoundationBlueprint::class);

        if ($this->app->bound(BusinessActionRegistry::class)) {
            $actions = $this->app->make(BusinessActionRegistry::class);
            foreach ([
                new ActionDefinition('titan_train.assignments.list', ListAssignments::class, 'low', false, 'titan_train.learning'),
                new ActionDefinition('titan_train.readiness.get', GetReadiness::class, 'low', false, 'titan_train.learning'),
                new ActionDefinition('titan_train.lesson.complete', CompleteLesson::class, 'low', false, 'titan_train.learning'),
                new ActionDefinition('titan_train.cleaner.assign', AssignCleaner::class, 'medium', true, 'titan_train.learning'),
            ] as $definition) {
                if (! $actions->has($definition->key)) {
                    $actions->register($definition);
                }
            }
        }
    }

    public function boot(): void
    {
        if (! (bool) config('titan_train.enabled', true)) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
        $this->loadRoutesFrom(__DIR__.'/../../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../Resources/views', 'titan-train');

        RateLimiter::for('titan-train', static fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        if ($this->app->bound(CapabilityRegistry::class)) {
            $registry = $this->app->make(CapabilityRegistry::class);
            if (! $registry->has('titan_train.learning')) {
                $registry->register(new CapabilityDefinition(
                    key: 'titan_train.learning', provider: 'TitanTrain', version: '0.1.0',
                    requires: [], metadata: ['online_only' => true, 'pwa' => true, 'vertical' => 'cleaning'],
                ));
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([SetupCleanerTrainingCommand::class]);
        }
    }
}
