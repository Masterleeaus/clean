<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Providers;

use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Intelligence\Actions\{
    AssignAgentToolAction, AssignSkillAction, CreateAgentAction, CreateCanvasAction, CreateConnectionAction,
    CreateFolderAction, CreateOnboardingFlowAction, CreateProviderConnectionAction, CreateRoutingPolicyAction,
    CreateShareAction, CreateSkillAction, CreateVoiceProfileAction, IntelligenceSummaryAction,
    PublishAnnouncementAction, RecordOnboardingProgressAction, StartCouncilRunAction, StartVoiceSessionAction,
    StoreMemoryAction, UpsertModelAction
};
use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Intelligence\Repositories\DatabaseIntelligenceRepository;
use Illuminate\Support\ServiceProvider;

final class TitanIntelligenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(IntelligenceRepository::class, DatabaseIntelligenceRepository::class);
    }

    public function boot(CapabilityRegistry $capabilities): void
    {
        foreach ($this->definitions() as $definition) $capabilities->register($definition);
    }

    /** @return array<int,array<string,mixed>> */
    private function definitions(): array
    {
        return [
            $this->definition('titan.workspace.folder.create', 'titan.workspace.write', CreateFolderAction::class, 'Create a company workspace folder.'),
            $this->definition('titan.workspace.canvas.create', 'titan.workspace.write', CreateCanvasAction::class, 'Create a canvas linked to a Meetup conversation or folder.'),
            $this->definition('titan.workspace.share.create', 'titan.workspace.write', CreateShareAction::class, 'Create an expiring hashed-token share grant.'),
            $this->definition('titan.memory.store', 'titan.memory.write', StoreMemoryAction::class, 'Store governed structured memory. Temporary chat requires promotion.'),
            $this->definition('titan.skills.create', 'titan.skills.manage', CreateSkillAction::class, 'Create a reusable versioned Titan skill.'),
            $this->definition('titan.skills.assign', 'titan.skills.manage', AssignSkillAction::class, 'Assign a registered skill to a user or agent.'),
            $this->definition('titan.agents.create', 'titan.agents.manage', CreateAgentAction::class, 'Create a governed company AI agent profile.'),
            $this->definition('titan.agents.tool.assign', 'titan.agents.manage', AssignAgentToolAction::class, 'Assign an existing registered Titan or WorkCore tool to an agent.'),
            $this->definition('titan.connectors.create', 'titan.connectors.manage', CreateConnectionAction::class, 'Create a disabled-by-default external connector using Titan Vault references.'),
            $this->definition('titan.providers.connection.create', 'titan.providers.manage', CreateProviderConnectionAction::class, 'Create an AI provider connection using Titan Vault references.'),
            $this->definition('titan.models.upsert', 'titan.providers.manage', UpsertModelAction::class, 'Register or update an available AI model.'),
            $this->definition('titan.routing.policy.create', 'titan.providers.manage', CreateRoutingPolicyAction::class, 'Create a cost, latency and capability routing policy.'),
            $this->definition('titan.model_council.start', 'titan.providers.manage', StartCouncilRunAction::class, 'Create an auditable model-council run without calling providers directly.'),
            $this->definition('titan.voice.profile.create', 'titan.voice.manage', CreateVoiceProfileAction::class, 'Create a provider-neutral voice profile.'),
            $this->definition('titan.voice.session.start', 'titan.voice.manage', StartVoiceSessionAction::class, 'Create a governed voice session record.'),
            $this->definition('titan.announcements.publish', 'titan.announcements.manage', PublishAnnouncementAction::class, 'Publish a company announcement.'),
            $this->definition('titan.onboarding.flow.create', 'titan.onboarding.write', CreateOnboardingFlowAction::class, 'Create a company onboarding flow and ordered steps.'),
            $this->definition('titan.onboarding.progress.record', 'titan.onboarding.write', RecordOnboardingProgressAction::class, 'Record member onboarding progress.'),
            $this->definition('titan.intelligence.summary', 'titan.intelligence.read', IntelligenceSummaryAction::class, 'Read a bounded company intelligence-runtime summary.'),
        ];
    }

    /** @return array<string,mixed> */
    private function definition(string $id, string $permission, string $handler, string $description): array
    {
        return [
            'id' => $id, 'provider' => 'titan-intelligence-runtime', 'version' => '0.5.0', 'kind' => 'first-party',
            'permission' => $permission, 'handler' => $handler, 'description' => $description,
            'input_schema' => ['type' => 'object', 'additionalProperties' => true],
        ];
    }
}
