<?php

declare(strict_types=1);

namespace TitanZero\Interaction\Wizard;

use TitanZero\Interaction\Contracts\CommandBusInterface;
use TitanZero\Interaction\Wizard\Command\CommandMapper;
use TitanZero\Interaction\Wizard\Guidance\LocalGuidanceProvider;
use TitanZero\Interaction\Wizard\Offline\LocalCommandOutbox;
use TitanZero\Interaction\Wizard\Validation\WizardValidationEngine;
use TitanZero\Interaction\Cognition\Events\CognitiveEvent;
use TitanZero\Interaction\Cognition\Events\CognitiveEventStoreInterface;
use TitanZero\Interaction\Cognition\Events\CognitiveEventType;

final class UniversalWizardEngine
{
    /**
     * $commandBus is intentionally nullable: passing null means "treat
     * this as offline, queue to the outbox" and passing a real bus means
     * "dispatch immediately." The bug this fix pass addresses was never
     * in this class — it's that the service provider always resolved a
     * real CommandBus via DI regardless of actual connectivity, making
     * the null branch unreachable in production even though it works
     * correctly here and is exercised by the test suite. See
     * InteractionServiceProvider's UniversalWizardEngine binding.
     */
    public function __construct(
        private readonly WizardRegistry $registry,
        private readonly WizardValidationEngine $validator,
        private readonly LocalGuidanceProvider $guidance,
        private readonly CommandMapper $commandMapper,
        private readonly LocalCommandOutbox $outbox,
        private readonly ?CommandBusInterface $commandBus = null,
        private readonly ?CognitiveEventStoreInterface $cognitiveEvents = null,
    ) {}

    public function start(string $wizardId, array $context = []): WizardSession
    {
        $session = new WizardSession(
            id: bin2hex(random_bytes(16)),
            definition: $this->registry->get($wizardId),
            context: $context,
        );
        $this->record(CognitiveEventType::ObservationRecorded, $session, ['wizard_started' => $wizardId]);
        return $session;
    }

    public function submitStep(WizardSession $session, array $input): WizardResult
    {
        if ($session->complete()) {
            return new WizardResult($session, true, guidance: 'This wizard is already complete.');
        }
        $step = $session->currentStep();
        if ($step === null) {
            throw new \LogicException('Wizard session points to a missing step.');
        }
        $errors = $this->validator->validateStep($step, $input, $session->data);
        if ($errors !== []) {
            return new WizardResult($session, errors: $errors, guidance: $this->guidance->guidance($step, $session->data, $errors));
        }
        $session->data = array_replace($session->data, $input);
        $session->history[] = [
            'step_id' => $step['id'],
            'input' => $input,
            'completed_at' => gmdate(DATE_ATOM),
        ];
        $session->stepIndex++;
        $this->skipConditionalSteps($session);
        if ($session->stepIndex >= $session->definition->stepCount()) {
            $session->status = 'completed';
            $command = $this->commandMapper->map($session);
            $command['payload']['_context'] = array_replace((array) ($command['payload']['_context'] ?? []), [
                'tenant_id' => $session->context['tenant_id'] ?? 'default',
                'user_id' => $session->context['user_id'] ?? null,
                'device_id' => $session->context['device_id'] ?? null,
                'wizard_run_id' => $session->id,
                'correlation_id' => $session->context['correlation_id'] ?? $session->id,
            ]);
            $this->record(CognitiveEventType::CommandPrepared, $session, ['command' => $command]);
            if ($this->commandBus !== null) {
                $this->commandBus->dispatch((string) $command['capability'], (array) $command['payload']);
                return new WizardResult($session, true, command: $command, guidance: 'Executed through the WorkCore command boundary.');
            }
            $this->outbox->enqueue($command);
            return new WizardResult($session, true, command: $command, guidance: 'Saved locally and queued for execution.');
        }
        $next = $session->currentStep() ?? [];
        return new WizardResult($session, guidance: $this->guidance->guidance($next, $session->data));
    }

    private function skipConditionalSteps(WizardSession $session): void
    {
        while (($step = $session->currentStep()) !== null && isset($step['when']) && !$this->matches((array) $step['when'], $session->data)) {
            $session->history[] = ['step_id' => $step['id'], 'skipped' => true, 'completed_at' => gmdate(DATE_ATOM)];
            $session->stepIndex++;
        }
    }

    private function matches(array $conditions, array $data): bool
    {
        foreach ($conditions as $key => $expected) {
            if (($data[$key] ?? null) !== $expected) {
                return false;
            }
        }
        return true;
    }

    private function record(CognitiveEventType $type, WizardSession $session, array $payload): void
    {
        if ($this->cognitiveEvents === null) {
            return;
        }
        $tenantId = (string) ($session->context['tenant_id'] ?? 'default');
        $event = CognitiveEvent::create(
            type: $type,
            tenantId: $tenantId,
            payload: $payload,
            userId: isset($session->context['user_id']) ? (string) $session->context['user_id'] : null,
            deviceId: isset($session->context['device_id']) ? (string) $session->context['device_id'] : null,
            teamId: isset($session->context['team_id']) ? (string) $session->context['team_id'] : null,
            wizardRunId: $session->id,
            correlationId: (string) ($session->context['correlation_id'] ?? $session->id),
            privacyClass: (string) ($session->context['privacy_class'] ?? 'tenant_private'),
            sequence: count($session->history),
        );
        $this->cognitiveEvents->append($event);
    }
}
