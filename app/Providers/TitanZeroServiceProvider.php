<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\TitanZero\TitanZeroFeatureFlags;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class TitanZeroServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2) . '/config/titan-zero.php', 'titan-zero');

        $this->app->singleton(
            TitanZeroFeatureFlags::class,
            static fn (Application $app): TitanZeroFeatureFlags => TitanZeroFeatureFlags::fromArray(
                (array) $app['config']->get('titan-zero', []),
            ),
        );

        $flags = $this->app->make(TitanZeroFeatureFlags::class);
        foreach ($flags->coreProviderClassNames() as $provider) {
            if (! class_exists($provider)) {
                throw new RuntimeException("Titan Zero provider [{$provider}] is enabled but not loadable.");
            }

            $this->app->register($provider);
        }
    }
}
