<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\WorkCore\WorkCoreServiceProvider;
use Illuminate\Routing\Route;
use Tests\TestCase;
use TitanZero\Interaction\Providers\InteractionServiceProvider;

final class HostBootTest extends TestCase
{
    public function test_authoritative_service_providers_are_loaded_once(): void
    {
        $loaded = $this->app->getLoadedProviders();

        self::assertTrue($loaded[WorkCoreServiceProvider::class] ?? false);
        self::assertTrue($loaded[InteractionServiceProvider::class] ?? false);
        self::assertSame(1, count(array_filter(
            array_keys($loaded),
            static fn (string $provider): bool => $provider === WorkCoreServiceProvider::class,
        )));
        self::assertSame(1, count(array_filter(
            array_keys($loaded),
            static fn (string $provider): bool => $provider === InteractionServiceProvider::class,
        )));
    }

    public function test_interaction_routes_boot_without_duplicate_names(): void
    {
        $routes = array_values(array_filter(
            $this->app['router']->getRoutes()->getRoutes(),
            static fn (Route $route): bool => str_starts_with((string) $route->getName(), 'interaction.')
                || str_starts_with((string) $route->getName(), 'api.interaction.'),
        ));

        self::assertNotEmpty($routes);

        $names = array_map(static fn (Route $route): string => (string) $route->getName(), $routes);
        self::assertSame($names, array_values(array_unique($names)));
    }

    public function test_interaction_engine_defaults_to_local_safe_operation(): void
    {
        self::assertTrue((bool) config('interaction.offline.enabled'));
        self::assertFalse((bool) config('interaction.ai.enabled'));
        self::assertTrue((bool) config('interaction.authority.default_deny'));
    }
}
