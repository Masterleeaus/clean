<?php

declare(strict_types=1);

namespace App\Titan\Creative\Repositories;

use App\Titan\Creative\Contracts\CreativeRepository;
use App\Titan\Creative\Domain\CampaignSchedulePolicy;
use App\Titan\Creative\Domain\ContentApprovalPolicy;
use App\Titan\Creative\Domain\GenerationJobState;
use App\Titan\Creative\Domain\ProviderReferencePolicy;
use App\Titan\Creative\Domain\PublicationState;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class DatabaseCreativeRepository implements CreativeRepository
{
    public function __construct(private readonly ConnectionInterface $db) {}

    public function createBrandKit(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_brand_kits', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'description' => $data['description'] ?? null,
            'colours' => $this->json($data['colours'] ?? null), 'typography' => $this->json($data['typography'] ?? null),
            'voice' => $this->json($data['voice'] ?? null), 'status' => 'active', 'created_by_user_id' => $actorId,
        ]);
    }

    public function createAudience(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_audiences', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'description' => $data['description'] ?? null,
            'traits' => $this->json($data['traits'] ?? null), 'channels' => $this->json($data['channels'] ?? null),
            'entity_type' => $data['entity_type'] ?? null,
            'entity_public_id' => $data['entity_public_id'] ?? null,
            'status' => 'active', 'created_by_user_id' => $actorId,
        ]);
    }

    public function createTemplate(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_content_templates', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'content_type' => $this->required($data, 'content_type'),
            'template_body' => $data['template_body'] ?? null, 'variables' => $this->json($data['variables'] ?? null),
            'version' => max(1, (int) ($data['version'] ?? 1)), 'status' => 'active', 'created_by_user_id' => $actorId,
        ]);
    }

    public function createProject(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_creative_projects', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'project_type' => $this->required($data, 'project_type'),
            'brand_kit_id' => $data['brand_kit_id'] ?? null, 'campaign_id' => $data['campaign_id'] ?? null,
            'brief' => $data['brief'] ?? null, 'status' => 'draft', 'owner_user_id' => $actorId,
        ]);
    }

    public function registerAsset(array $data, int $companyId, int $actorId): array
    {
        $reference = $this->required($data, 'storage_reference');
        if (str_starts_with($reference, 'http://') || str_starts_with($reference, 'https://')) {
            throw new InvalidArgumentException('Creative assets require a private storage reference.');
        }
        return $this->insertAggregate('titan_creative_assets', $data, $companyId, $actorId, [
            'project_id' => $data['project_id'] ?? null, 'asset_type' => $this->required($data, 'asset_type'),
            'name' => $this->required($data, 'name'), 'storage_reference' => $reference,
            'mime_type' => $data['mime_type'] ?? null, 'size_bytes' => $data['size_bytes'] ?? null,
            'checksum_sha256' => $data['checksum_sha256'] ?? null, 'source_type' => (string) ($data['source_type'] ?? 'uploaded'),
            'status' => 'active', 'created_by_user_id' => $actorId,
        ]);
    }

    public function queueGeneration(array $data, int $companyId, int $actorId): array
    {
        $jobType = $this->required($data, 'job_type');
        $reference = isset($data['provider_connection_public_id']) ? (string) $data['provider_connection_public_id'] : null;
        if (! ProviderReferencePolicy::valid($jobType, $reference)) {
            throw new InvalidArgumentException('Configured provider reference is required.');
        }
        if (ProviderReferencePolicy::requiresReference($jobType)) {
            $this->assertProviderReference($companyId, (string) $reference);
        }
        return $this->insertAggregate('titan_generation_jobs', $data, $companyId, $actorId, [
            'project_id' => $data['project_id'] ?? null, 'job_type' => $jobType,
            'provider_connection_public_id' => $reference, 'model_key' => $data['model_key'] ?? null,
            'prompt_hash' => isset($data['prompt']) ? hash('sha256', (string) $data['prompt']) : null,
            'parameters' => $this->json($data['parameters'] ?? null), 'status' => 'queued',
            'estimated_cost_minor' => $data['estimated_cost_minor'] ?? null, 'created_by_user_id' => $actorId,
        ]);
    }

    public function transitionGeneration(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $record = $this->byPublicId('titan_generation_jobs', $companyId, $this->required($data, 'public_id'));
        $to = strtolower($this->required($data, 'status'));
        if (! GenerationJobState::canTransition((string) $record->status, $to)) {
            throw new InvalidArgumentException('Invalid generation job transition.');
        }
        $update = ['status' => $to, 'updated_at' => now()];
        if ($to === 'processing') $update['started_at'] = now();
        if (GenerationJobState::terminal($to)) $update['completed_at'] = now();
        if ($to === 'failed') $update['error_code'] = $data['error_code'] ?? 'GENERATION_FAILED';
        if (isset($data['actual_cost_minor'])) $update['actual_cost_minor'] = max(0, (int) $data['actual_cost_minor']);
        $this->db->table('titan_generation_jobs')->where('company_id', $companyId)->where('id', $record->id)->update($update);
        return $this->snapshot('titan_generation_jobs', $companyId, (string) $record->public_id);
    }

    public function createCampaign(array $data, int $companyId, int $actorId): array
    {
        $start = isset($data['start_date']) ? (string) $data['start_date'] : null;
        $end = isset($data['end_date']) ? (string) $data['end_date'] : null;
        if (! CampaignSchedulePolicy::valid($start, $end)) throw new InvalidArgumentException('Campaign dates are invalid.');
        return $this->insertAggregate('titan_campaigns', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'brand_kit_id' => $data['brand_kit_id'] ?? null,
            'audience_id' => $data['audience_id'] ?? null, 'campaign_type' => $this->required($data, 'campaign_type'),
            'start_date' => $start, 'end_date' => $end, 'budget_minor' => $data['budget_minor'] ?? null,
            'currency' => strtoupper((string) ($data['currency'] ?? 'AUD')), 'status' => 'draft', 'owner_user_id' => $actorId,
        ]);
    }

    public function createContent(array $data, int $companyId, int $actorId): array
    {
        $required = (bool) ($data['approval_required'] ?? true);
        return $this->insertAggregate('titan_content_items', $data, $companyId, $actorId, [
            'campaign_id' => $data['campaign_id'] ?? null, 'project_id' => $data['project_id'] ?? null,
            'template_id' => $data['template_id'] ?? null, 'content_type' => $this->required($data, 'content_type'),
            'title' => $data['title'] ?? null, 'body' => $data['body'] ?? null,
            'asset_public_ids' => $this->json($data['asset_public_ids'] ?? null), 'approval_required' => $required,
            'approval_status' => ContentApprovalPolicy::approvalStatus($required, null), 'status' => 'draft',
            'created_by_user_id' => $actorId,
        ]);
    }

    public function scheduleContent(array $data, int $companyId, int $actorId): array
    {
        $content = $this->byPublicId('titan_content_items', $companyId, $this->required($data, 'content_public_id'));
        return $this->insertAggregate('titan_content_calendar_entries', $data, $companyId, $actorId, [
            'content_item_id' => $content->id, 'scheduled_at' => $this->required($data, 'scheduled_at'),
            'timezone' => (string) ($data['timezone'] ?? 'Australia/Sydney'), 'status' => 'scheduled',
            'created_by_user_id' => $actorId,
        ]);
    }

    public function createPublicationChannel(array $data, int $companyId, int $actorId): array
    {
        $type = strtolower($this->required($data, 'channel_type'));
        $reference = isset($data['connection_public_id']) ? trim((string) $data['connection_public_id']) : '';
        if ($type !== 'internal' && $reference === '') throw new InvalidArgumentException('External channel requires a configured connector.');
        if ($reference !== '') $this->assertConnectionReference($companyId, $reference);
        return $this->insertAggregate('titan_publication_channels', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'channel_type' => $type,
            'connection_public_id' => $reference !== '' ? $reference : null,
            'external_account_reference' => $data['external_account_reference'] ?? null,
            'status' => $type === 'internal' ? 'active' : 'disabled',
            'configuration' => $this->json($data['configuration'] ?? null), 'created_by_user_id' => $actorId,
        ]);
    }

    public function requestPublication(array $data, int $companyId, int $actorId): array
    {
        $content = $this->byPublicId('titan_content_items', $companyId, $this->required($data, 'content_public_id'));
        if (! ContentApprovalPolicy::mayPublish((bool) $content->approval_required, (string) $content->approval_status)) {
            throw new InvalidArgumentException('Content approval is required before publication.');
        }
        $channel = $this->byPublicId('titan_publication_channels', $companyId, $this->required($data, 'channel_public_id'));
        if ((string) $channel->status !== 'active') throw new InvalidArgumentException('Publication channel is not active.');
        return $this->insertAggregate('titan_publications', $data, $companyId, $actorId, [
            'content_item_id' => $content->id, 'publication_channel_id' => $channel->id,
            'calendar_entry_id' => $data['calendar_entry_id'] ?? null,
            'status' => isset($data['scheduled_at']) ? 'scheduled' : 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null, 'created_by_user_id' => $actorId,
        ]);
    }

    public function transitionPublication(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $record = $this->byPublicId('titan_publications', $companyId, $this->required($data, 'public_id'));
        $to = strtolower($this->required($data, 'status'));
        if (! PublicationState::canTransition((string) $record->status, $to)) throw new InvalidArgumentException('Invalid publication transition.');
        $update = ['status' => $to, 'updated_at' => now()];
        if ($to === 'published') { $update['published_at'] = now(); $update['provider_reference'] = $data['provider_reference'] ?? null; $update['permalink'] = $data['permalink'] ?? null; }
        if ($to === 'retracted') $update['retracted_at'] = now();
        if ($to === 'failed') $update['error_code'] = $data['error_code'] ?? 'PUBLICATION_FAILED';
        $this->db->table('titan_publications')->where('company_id', $companyId)->where('id', $record->id)->update($update);
        return $this->snapshot('titan_publications', $companyId, (string) $record->public_id);
    }

    public function createAutomation(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_marketing_automations', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'trigger_type' => $this->required($data, 'trigger_type'),
            'trigger_configuration' => $this->json($data['trigger_configuration'] ?? null),
            'actions' => $this->json($data['actions'] ?? []), 'status' => 'draft', 'created_by_user_id' => $actorId,
        ]);
    }

    public function createSeoBrief(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_seo_briefs', $data, $companyId, $actorId, [
            'campaign_id' => $data['campaign_id'] ?? null, 'topic' => $this->required($data, 'topic'),
            'primary_keyword' => $data['primary_keyword'] ?? null,
            'secondary_keywords' => $this->json($data['secondary_keywords'] ?? null),
            'search_intent' => $data['search_intent'] ?? null, 'outline' => $data['outline'] ?? null,
            'source_references' => $this->json($data['source_references'] ?? null),
            'status' => 'draft', 'created_by_user_id' => $actorId,
        ]);
    }

    public function createNewsletter(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_newsletters', $data, $companyId, $actorId, [
            'campaign_id' => $data['campaign_id'] ?? null, 'audience_id' => $data['audience_id'] ?? null,
            'subject' => $this->required($data, 'subject'), 'preview_text' => $data['preview_text'] ?? null,
            'body' => $data['body'] ?? null, 'status' => 'draft', 'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by_user_id' => $actorId,
        ]);
    }

    public function decideApproval(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $content = $this->byPublicId('titan_content_items', $companyId, $this->required($data, 'content_public_id'));
        $decision = strtolower($this->required($data, 'decision'));
        if (! in_array($decision, ['approved', 'rejected'], true)) throw new InvalidArgumentException('Approval decision is invalid.');
        $this->db->transaction(function () use ($companyId, $actorId, $content, $decision, $data): void {
            $this->db->table('titan_content_approvals')->insert([
                'company_id' => $companyId, 'content_item_id' => $content->id, 'decision' => $decision,
                'comment' => $data['comment'] ?? null, 'decided_by_user_id' => $actorId,
                'decided_at' => now(), 'created_at' => now(),
            ]);
            $this->db->table('titan_content_items')->where('company_id', $companyId)->where('id', $content->id)->update([
                'approval_status' => $decision, 'status' => $decision === 'approved' ? 'approved' : 'draft', 'updated_at' => now(),
            ]);
        }, 3);
        return $this->snapshot('titan_content_items', $companyId, (string) $content->public_id);
    }

    public function recordAnalytics(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $id = $this->db->table('titan_analytics_observations')->insertGetId([
            'company_id' => $companyId, 'subject_type' => $this->required($data, 'subject_type'),
            'subject_public_id' => $this->required($data, 'subject_public_id'), 'metric_key' => $this->required($data, 'metric_key'),
            'metric_value' => (float) ($data['metric_value'] ?? 0), 'unit' => $data['unit'] ?? null,
            'source_type' => $this->required($data, 'source_type'), 'source_reference' => $data['source_reference'] ?? null,
            'observed_at' => $data['observed_at'] ?? now(), 'dimensions' => $this->json($data['dimensions'] ?? null), 'created_at' => now(),
        ]);
        return ['id' => $id, 'subject_public_id' => (string) $data['subject_public_id'], 'metric_key' => (string) $data['metric_key']];
    }

    public function summary(int $companyId): array
    {
        $this->assertCompany($companyId);
        $tables = [
            'brand_kits' => 'titan_brand_kits', 'audiences' => 'titan_audiences', 'projects' => 'titan_creative_projects',
            'assets' => 'titan_creative_assets', 'generation_queued' => 'titan_generation_jobs', 'campaigns' => 'titan_campaigns',
            'content_pending_approval' => 'titan_content_items', 'publications_pending' => 'titan_publications',
            'automations' => 'titan_marketing_automations', 'seo_briefs' => 'titan_seo_briefs', 'newsletters' => 'titan_newsletters',
        ];
        $summary = [];
        foreach ($tables as $key => $table) {
            if (! $this->db->getSchemaBuilder()->hasTable($table)) { $summary[$key] = null; continue; }
            $query = $this->db->table($table)->where('company_id', $companyId);
            if ($key === 'generation_queued') $query->whereIn('status', ['queued','processing']);
            if ($key === 'content_pending_approval') $query->where('approval_required', true)->where('approval_status', 'pending');
            if ($key === 'publications_pending') $query->whereIn('status', ['draft','scheduled','publishing']);
            $summary[$key] = $query->count();
        }
        return $summary;
    }

    /** @param array<string,mixed> $fields */
    private function insertAggregate(string $table, array $data, int $companyId, int $actorId, array $fields): array
    {
        $this->guard($companyId, $actorId);
        $publicId = (string) ($data['public_id'] ?? Str::ulid());
        $record = array_merge(['public_id' => $publicId, 'company_id' => $companyId], $fields, [
            'metadata' => array_key_exists('metadata', $fields) ? $fields['metadata'] : $this->json($data['metadata'] ?? null),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $columns = $this->db->getSchemaBuilder()->getColumnListing($table);
        $record = array_intersect_key($record, array_flip($columns));
        $id = $this->db->table($table)->insertGetId($record);
        return $this->snapshot($table, $companyId, $publicId) + ['id' => $id];
    }

    private function snapshot(string $table, int $companyId, string $publicId): array
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->first();
        if ($record === null) throw new \RuntimeException('Creative or marketing record was not found.');
        return (array) $record;
    }

    private function byPublicId(string $table, int $companyId, string $publicId): object
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->first();
        if ($record === null) throw new InvalidArgumentException('Referenced creative or marketing record was not found.');
        return $record;
    }

    private function assertProviderReference(int $companyId, string $publicId): void
    {
        $record = $this->db->table('titan_provider_connections')->where('company_id', $companyId)->where('public_id', $publicId)->whereIn('status', ['active','ready'])->first();
        if ($record === null) throw new InvalidArgumentException('Configured AI provider connection was not found.');
    }

    private function assertConnectionReference(int $companyId, string $publicId): void
    {
        $record = $this->db->table('titan_connections')->where('company_id', $companyId)->where('public_id', $publicId)->where('enabled', true)->first();
        if ($record === null) throw new InvalidArgumentException('Enabled publication connector was not found.');
    }

    private function guard(int $companyId, int $actorId): void
    {
        $this->assertCompany($companyId);
        if ($actorId < 1) throw new InvalidArgumentException('Active actor is required.');
    }

    private function assertCompany(int $companyId): void
    {
        if ($companyId < 1) throw new InvalidArgumentException('Active company is required.');
    }

    private function required(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));
        if ($value === '') throw new InvalidArgumentException("{$key} is required.");
        return $value;
    }

    private function json(mixed $value): ?string
    {
        return $value === null ? null : json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
