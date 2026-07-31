<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Marketplace\MarketplaceServiceProvider;
use App\Support\Extensions\CatalogExtensionProviderResolver;
use App\Support\Extensions\ExtensionCatalog;
use App\Support\Extensions\ExtensionProviderResolver;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExtensionCatalog::class, static function (Application $app): ExtensionCatalog {
            return new ExtensionCatalog(
                extensionsPath: $app->path('Extensions'),
                enabledIdentifiers: array_values((array) $app['config']->get('titan-zero.extensions.enabled', [])),
                legacyProviders: MarketplaceServiceProvider::getExtensionProviders(),
            );
        });

        $this->app->singleton(
            ExtensionProviderResolver::class,
            static fn (Application $app): ExtensionProviderResolver =>
                new CatalogExtensionProviderResolver($app->make(ExtensionCatalog::class)),
        );

        $resolver = $this->app->make(ExtensionProviderResolver::class);
        foreach ($resolver->resolveEnabledProviders() as $provider) {
            if ($this->app->getProvider($provider) === null) {
                $this->app->register($provider);
            }
        }
    }
}
