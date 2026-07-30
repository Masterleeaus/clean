<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Interaction;

use LogicException;
use TitanZero\Interaction\Wizard\WizardRegistry;

final class TitanTrainWizardContributor
{
    public function __construct(private TitanTrainInteractionCatalog $catalog) {}

    public function register(WizardRegistry $registry): int
    {
        $count = 0;

        foreach ($this->catalog->all() as $definition) {
            $id = (string) $definition['key'];
            if ($registry->has($id)) {
                throw new LogicException("Titan Train interaction [{$id}] is already registered.");
            }

            $registry->register($this->toWizardDefinition($definition));
            $count++;
        }

        return $count;
    }

    /** @param array<string, mixed> $definition */
    private function toWizardDefinition(array $definition): array
    {
        $id = (string) $definition['key'];
        [$name, $roles] = match ($id) {
            'titan_train.lesson.guided' => ['Guided lesson', ['learner']],
            'titan_train.assessment.knowledge' => ['Knowledge assessment', ['learner']],
            'titan_train.assessment.practical' => ['Practical observation', ['trainer']],
            'titan_train.induction.property' => ['Property induction', ['learner']],
            default => throw new LogicException("Unsupported Titan Train interaction [{$id}]."),
        };

        return [
            'id' => $id,
            'version' => sprintf('%d.0.0', (int) $definition['version']),
            'name' => $name,
            'capability' => 'titan_train.learning',
            'steps' => array_map(
                static fn (array $step): array => [
                    'id' => (string) $step['key'],
                    'type' => (string) $step['type'],
                    'required' => (bool) $step['required'],
                    'confirmation' => (array) $step['confirmation'],
                ],
                (array) $definition['steps'],
            ),
            'permissions' => ['required_roles' => $roles],
            'metadata' => [
                'learning_authority' => $definition['learning_authority'],
                'runtime_authority' => $definition['runtime_authority'],
                'required_context' => $definition['required_context'],
                'allowed_commands' => $definition['allowed_commands'],
                'completion' => $definition['completion'],
            ],
            'offline' => [
                'enabled' => false,
                'policy' => 'online_required',
            ],
        ];
    }
}
