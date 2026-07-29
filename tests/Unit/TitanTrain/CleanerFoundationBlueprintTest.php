<?php

namespace Tests\Unit\TitanTrain;

use App\Domains\TitanTrain\Domain\Blueprints\CleanerFoundationBlueprint;
use PHPUnit\Framework\TestCase;

final class CleanerFoundationBlueprintTest extends TestCase
{
    public function test_cleaner_foundation_has_expected_shape(): void
    {
        $blueprint = new CleanerFoundationBlueprint();

        self::assertSame('cleaner-foundation-au', $blueprint->key());
        self::assertCount(9, $blueprint->modules());
        self::assertSame(26, array_sum(array_map(static fn (array $module): int => count($module['lessons']), $blueprint->modules())));
        self::assertCount(2, $blueprint->assessments());
        self::assertCount(5, $blueprint->competencies());
    }
}
