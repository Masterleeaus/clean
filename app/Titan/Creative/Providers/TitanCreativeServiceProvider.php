<?php

declare(strict_types=1);

namespace App\Titan\Creative\Providers;

use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Creative\Actions\{
    CreateBrandKitAction, CreateAudienceAction, CreateTemplateAction, CreateProjectAction, RegisterAssetAction,
    QueueGenerationAction, TransitionGenerationAction, CreateCampaignAction, CreateContentAction, ScheduleContentAction,
    CreatePublicationChannelAction, RequestPublicationAction, TransitionPublicationAction, CreateAutomationAction,
    CreateSeoBriefAction, CreateNewsletterAction, DecideApprovalAction, RecordAnalyticsAction, CreativeSummaryAction
};
use App\Titan\Creative\Contracts\CreativeRepository;
use App\Titan\Creative\Repositories\DatabaseCreativeRepository;
use Illuminate\Support\ServiceProvider;

final class TitanCreativeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CreativeRepository::class, DatabaseCreativeRepository::class);
    }

    public function boot(CapabilityRegistry $capabilities): void
    {
        foreach ($this->definitions() as $definition) $capabilities->register($definition);
    }

    /** @return array<int,array<string,mixed>> */
    private function definitions(): array
    {
        return [
            $this->definition('titan.brand_kit.create', 'titan.brand.manage', CreateBrandKitAction::class, 'Create a company brand kit.'),
            $this->definition('titan.audience.create', 'titan.marketing.manage', CreateAudienceAction::class, 'Create a marketing audience without copying CRM ownership.'),
            $this->definition('titan.content_template.create', 'titan.creative.manage', CreateTemplateAction::class, 'Create a versioned content template.'),
            $this->definition('titan.creative.project.create', 'titan.creative.manage', CreateProjectAction::class, 'Create a governed creative project.'),
            $this->definition('titan.creative.asset.register', 'titan.creative.manage', RegisterAssetAction::class, 'Register a privately stored creative asset.'),
            $this->definition('titan.creative.generation.queue', 'titan.creative.generate', QueueGenerationAction::class, 'Queue a provider-referenced creative generation record.'),
            $this->definition('titan.creative.generation.transition', 'titan.creative.generate', TransitionGenerationAction::class, 'Transition a creative generation lifecycle.'),
            $this->definition('titan.marketing.campaign.create', 'titan.marketing.manage', CreateCampaignAction::class, 'Create a marketing campaign plan.'),
            $this->definition('titan.marketing.content.create', 'titan.marketing.manage', CreateContentAction::class, 'Create an approval-aware content item.'),
            $this->definition('titan.marketing.calendar.schedule', 'titan.marketing.manage', ScheduleContentAction::class, 'Schedule content on the company calendar.'),
            $this->definition('titan.marketing.channel.create', 'titan.marketing.channels.manage', CreatePublicationChannelAction::class, 'Create a disabled-by-default external or internal publication channel.'),
            $this->definition('titan.marketing.publication.request', 'titan.marketing.publish', RequestPublicationAction::class, 'Request publication of approved content.'),
            $this->definition('titan.marketing.publication.transition', 'titan.marketing.publish', TransitionPublicationAction::class, 'Transition publication state with immutable published history.'),
            $this->definition('titan.marketing.automation.create', 'titan.marketing.automations.manage', CreateAutomationAction::class, 'Create a disabled draft marketing automation.'),
            $this->definition('titan.marketing.seo_brief.create', 'titan.marketing.manage', CreateSeoBriefAction::class, 'Create an evidence-referenced SEO brief.'),
            $this->definition('titan.marketing.newsletter.create', 'titan.marketing.manage', CreateNewsletterAction::class, 'Create a newsletter draft.'),
            $this->definition('titan.marketing.approval.decide', 'titan.marketing.approve', DecideApprovalAction::class, 'Approve or reject a content item.'),
            $this->definition('titan.marketing.analytics.record', 'titan.marketing.analytics.write', RecordAnalyticsAction::class, 'Append a source-attributed analytics observation.'),
            $this->definition('titan.creative.summary', 'titan.creative.read', CreativeSummaryAction::class, 'Read a bounded creative and marketing summary.'),
        ];
    }

    /** @return array<string,mixed> */
    private function definition(string $id, string $permission, string $handler, string $description): array
    {
        return [
            'id' => $id, 'provider' => 'titan-creative-marketing', 'version' => '0.6.0', 'kind' => 'first-party',
            'permission' => $permission, 'handler' => $handler, 'description' => $description,
            'input_schema' => ['type' => 'object', 'additionalProperties' => true],
        ];
    }
}
