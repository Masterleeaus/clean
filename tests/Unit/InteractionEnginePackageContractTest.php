<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class InteractionEnginePackageContractTest extends TestCase
{
    public function test_host_uses_one_explicit_interaction_engine_provider_registration(): void
    {
        $root = dirname(__DIR__, 2);
        $package = json_decode(
            file_get_contents($root . '/packages/titanzero/interaction-engine/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $appConfig = file_get_contents($root . '/config/app.php');

        self::assertSame([], $package['extra']['laravel']['providers'] ?? []);
        self::assertSame(
            1,
            substr_count(
                $appConfig,
                'TitanZero\\Interaction\\Providers\\InteractionServiceProvider::class',
            ),
        );
    }
}
