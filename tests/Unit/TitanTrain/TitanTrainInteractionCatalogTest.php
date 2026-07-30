<?php

declare(strict_types=1);

namespace Tests\Unit\TitanTrain;

use App\Domains\TitanTrain\Interaction\TitanTrainInteractionCatalog;
use PHPUnit\Framework\TestCase;

final class TitanTrainInteractionCatalogTest extends TestCase
{
    public function test_catalog_defines_four_safe_online_interactions(): void
    {
        $definitions = (new TitanTrainInteractionCatalog())->all();

        self::assertCount(4, $definitions);
        self::assertCount(4, array_unique(array_column($definitions, 'key')));

        foreach ($definitions as $definition) {
            self::assertSame('TitanTrain', $definition['learning_authority']);
            self::assertSame('InteractionEngine', $definition['runtime_authority']);
            self::assertTrue($definition['online_only']);
            self::assertTrue($definition['completion']['requires_idempotency_key']);

            $encoded = json_encode($definition, JSON_THROW_ON_ERROR);
            self::assertDoesNotMatchRegularExpression('/\b(?:php|javascript|sql|callback|class)\b/i', $encoded);
            self::assertDoesNotMatchRegularExpression('/\b(?:tt_|tz_|workcore_)[a-z0-9_]+\b/i', $encoded);
        }
    }

    public function test_guided_lesson_can_only_request_the_registered_completion_action(): void
    {
        $definition = (new TitanTrainInteractionCatalog())->get('titan_train.lesson.guided');

        self::assertSame(['titan_train.lesson.complete'], $definition['allowed_commands']);
    }

    public function test_practical_observation_requires_trainer_confirmation(): void
    {
        $definition = (new TitanTrainInteractionCatalog())->get('titan_train.assessment.practical');
        $steps = array_column($definition['steps'], null, 'key');

        self::assertTrue($steps['trainer_confirmation']['confirmation']['required']);
        self::assertSame('trainer', $steps['trainer_confirmation']['confirmation']['role']);
    }
}
