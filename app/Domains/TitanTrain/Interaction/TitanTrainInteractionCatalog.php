<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Interaction;

use InvalidArgumentException;

final class TitanTrainInteractionCatalog
{
    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return [
            $this->guidedLesson(),
            $this->knowledgeAssessment(),
            $this->practicalObservation(),
            $this->propertyInduction(),
        ];
    }

    /** @return array<string, mixed> */
    public function get(string $key): array
    {
        foreach ($this->all() as $definition) {
            if ($definition['key'] === $key) {
                return $definition;
            }
        }

        throw new InvalidArgumentException("Unknown Titan Train interaction definition [{$key}].");
    }

    /** @return array<string, mixed> */
    private function guidedLesson(): array
    {
        return $this->definition(
            key: 'titan_train.lesson.guided',
            context: ['company_public_id', 'actor_public_id', 'assignment_public_id', 'lesson_public_id'],
            steps: [
                $this->step('orient', 'summary'),
                $this->step('teach', 'lesson_content'),
                $this->step('check_understanding', 'knowledge_check'),
                $this->step('confirm_completion', 'confirmation', true),
            ],
            allowedCommands: ['titan_train.lesson.complete'],
        );
    }

    /** @return array<string, mixed> */
    private function knowledgeAssessment(): array
    {
        return $this->definition(
            key: 'titan_train.assessment.knowledge',
            context: ['company_public_id', 'actor_public_id', 'assignment_public_id', 'assessment_public_id'],
            steps: [
                $this->step('instructions', 'assessment_instructions'),
                $this->step('questions', 'question_set'),
                $this->step('review_answers', 'answer_review'),
                $this->step('submit_attempt', 'confirmation', true),
            ],
        );
    }

    /** @return array<string, mixed> */
    private function practicalObservation(): array
    {
        return $this->definition(
            key: 'titan_train.assessment.practical',
            context: ['company_public_id', 'actor_public_id', 'assignment_public_id', 'assessment_public_id', 'trainer_public_id'],
            steps: [
                $this->step('briefing', 'practical_briefing'),
                $this->step('observation_checklist', 'trainer_checklist'),
                $this->step('evidence_review', 'evidence_review'),
                $this->step('trainer_confirmation', 'approval', true, 'trainer'),
            ],
        );
    }

    /** @return array<string, mixed> */
    private function propertyInduction(): array
    {
        return $this->definition(
            key: 'titan_train.induction.property',
            context: ['company_public_id', 'actor_public_id', 'assignment_public_id', 'property_public_id'],
            steps: [
                $this->step('property_context', 'property_summary'),
                $this->step('site_rules', 'induction_content'),
                $this->step('hazards', 'hazard_acknowledgement'),
                $this->step('confirm_induction', 'confirmation', true),
            ],
            allowedCommands: ['titan_train.lesson.complete'],
        );
    }

    /**
     * @param list<string> $context
     * @param list<array<string, mixed>> $steps
     * @param list<string> $allowedCommands
     * @return array<string, mixed>
     */
    private function definition(string $key, array $context, array $steps, array $allowedCommands = []): array
    {
        return [
            'key' => $key,
            'version' => 1,
            'learning_authority' => 'TitanTrain',
            'runtime_authority' => 'InteractionEngine',
            'online_only' => true,
            'required_context' => $context,
            'steps' => $steps,
            'allowed_commands' => $allowedCommands,
            'completion' => [
                'record_authority' => 'TitanTrain',
                'audit_authority' => 'WorkCore',
                'requires_idempotency_key' => true,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function step(
        string $key,
        string $type,
        bool $requiresConfirmation = false,
        string $confirmationRole = 'learner',
    ): array {
        return [
            'key' => $key,
            'type' => $type,
            'required' => true,
            'confirmation' => [
                'required' => $requiresConfirmation,
                'role' => $confirmationRole,
            ],
        ];
    }
}
