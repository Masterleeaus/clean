<?php

declare(strict_types=1);

namespace Tests\Unit\TitanTrain;

use App\Domains\TitanTrain\Interaction\TitanTrainInteractionCatalog;
use App\Domains\TitanTrain\Interaction\TitanTrainWizardContributor;
use LogicException;
use PHPUnit\Framework\TestCase;
use TitanZero\Interaction\Wizard\WizardRegistry;

final class TitanTrainWizardContributorTest extends TestCase
{
    public function test_it_registers_the_catalog_in_the_canonical_wizard_registry(): void
    {
        $registry = new WizardRegistry();
        $contributor = new TitanTrainWizardContributor(new TitanTrainInteractionCatalog());

        self::assertSame(4, $contributor->register($registry));

        $guided = $registry->get('titan_train.lesson.guided');
        self::assertSame('Guided lesson', $guided->name);
        self::assertSame('titan_train.learning', $guided->capability);
        self::assertSame(['titan_train.lesson.complete'], $guided->metadata['allowed_commands']);
        self::assertFalse($guided->offline['enabled']);
        self::assertSame('online_required', $guided->offline['policy']);

        $practical = $registry->get('titan_train.assessment.practical');
        self::assertSame(['trainer'], $practical->permissions);
    }

    public function test_it_rejects_duplicate_registration(): void
    {
        $registry = new WizardRegistry();
        $contributor = new TitanTrainWizardContributor(new TitanTrainInteractionCatalog());
        $contributor->register($registry);

        $this->expectException(LogicException::class);
        $contributor->register($registry);
    }
}
