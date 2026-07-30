<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Repositories;

use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Intelligence\Domain\MemoryRetentionPolicy;
use App\Titan\Intelligence\Domain\ShareToken;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class DatabaseIntelligenceRepository implements IntelligenceRepository
{
    public function __construct(private readonly ConnectionInterface $db) {}

    public function createFolder(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_workspace_folders', $data, $companyId, $actorId, [
            'parent_id' => $data['parent_id'] ?? null, 'owner_user_id' => $actorId,
            'name' => $this->required($data, 'name'), 'description' => $data['description'] ?? null,
            'position' => max(0, (int) ($data['position'] ?? 0)), 'status' => 'active',
        ]);
    }

    public function createCanvas(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_workspace_canvases', $data, $companyId, $actorId, [
            'folder_id' => $data['folder_id'] ?? null, 'conversation_id' => $data['conversation_id'] ?? null,
            'title' => $this->required($data, 'title'), 'content' => $data['content'] ?? null,
            'content_format' => (string) ($data['content_format'] ?? 'tiptap-json'), 'version' => 1,
            'temporary' => (bool) ($data['temporary'] ?? false), 'status' => 'active',
            'created_by_user_id' => $actorId, 'updated_by_user_id' => $actorId,
        ]);
    }

    public function createShare(array $data, int $companyId, int $actorId): array
    {
        $issued = ShareToken::issue();
        $record = $this->insertAggregate('titan_workspace_shares', $data, $companyId, $actorId, [
            'subject_type' => $this->required($data, 'subject_type'),
            'subject_public_id' => $this->required($data, 'subject_public_id'),
            'token_hash' => $issued['hash'], 'permission' => (string) ($data['permission'] ?? 'view'),
            'expires_at' => $data['expires_at'] ?? null, 'created_by_user_id' => $actorId,
        ]);
        $record['token'] = $issued['token'];
        return $record;
    }

    public function storeMemory(array $data, int $companyId, int $actorId): array
    {
        $temporary = (bool) ($data['temporary_conversation'] ?? false);
        $promoted = (bool) ($data['explicitly_promoted'] ?? false);
        if (! MemoryRetentionPolicy::mayPersist($temporary, $promoted)) {
            throw new InvalidArgumentException('Temporary conversation memory requires explicit promotion.');
        }
        $retention = isset($data['retention_days']) ? (int) $data['retention_days'] : null;
        $expires = MemoryRetentionPolicy::expiresAt(new \DateTimeImmutable('now'), $retention);
        return $this->insertAggregate('titan_memories', $data, $companyId, $actorId, [
            'subject_type' => $this->required($data, 'subject_type'), 'subject_id' => $data['subject_id'] ?? null,
            'conversation_id' => $data['conversation_id'] ?? null, 'agent_public_id' => $data['agent_public_id'] ?? null,
            'memory_type' => $this->required($data, 'memory_type'), 'scope' => (string) ($data['scope'] ?? 'company'),
            'title' => $data['title'] ?? null, 'content' => $this->required($data, 'content'),
            'source_type' => $this->required($data, 'source_type'), 'source_reference' => $data['source_reference'] ?? null,
            'confidence' => MemoryRetentionPolicy::normaliseConfidence((float) ($data['confidence'] ?? 1.0)),
            'sensitivity' => (string) ($data['sensitivity'] ?? 'normal'), 'retention_days' => $retention,
            'expires_at' => $expires?->format('Y-m-d H:i:s'), 'promoted_from_temporary' => $temporary && $promoted,
            'status' => 'active', 'created_by_user_id' => $actorId,
        ]);
    }

    public function createSkill(array $data, int $companyId, int $actorId): array
    {
        return $this->db->transaction(function () use ($data, $companyId, $actorId): array {
            $skill = $this->insertAggregate('titan_skills', $data, $companyId, $actorId, [
                'slug' => Str::slug($this->required($data, 'slug')), 'name' => $this->required($data, 'name'),
                'description' => $data['description'] ?? null, 'visibility' => (string) ($data['visibility'] ?? 'company'),
                'status' => 'draft', 'current_version' => 1, 'created_by_user_id' => $actorId,
            ]);
            $this->db->table('titan_skill_versions')->insert([
                'company_id' => $companyId, 'skill_id' => $skill['id'], 'version' => 1,
                'instructions' => $this->required($data, 'instructions'),
                'input_schema' => $this->json($data['input_schema'] ?? null), 'output_schema' => $this->json($data['output_schema'] ?? null),
                'provenance' => $this->json($data['provenance'] ?? null),
                'checksum' => hash('sha256', (string) $data['instructions']), 'created_by_user_id' => $actorId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return $skill;
        }, 3);
    }

    public function assignSkill(array $data, int $companyId, int $actorId): array
    {
        $skill = $this->byPublicId('titan_skills', $companyId, $this->required($data, 'skill_public_id'));
        $this->db->table('titan_skill_assignments')->updateOrInsert([
            'company_id' => $companyId, 'skill_id' => $skill->id,
            'assignee_type' => $this->required($data, 'assignee_type'), 'assignee_id' => $this->required($data, 'assignee_id'),
        ], [
            'enabled' => (bool) ($data['enabled'] ?? true), 'configuration' => $this->json($data['configuration'] ?? null),
            'assigned_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return ['skill_public_id' => $skill->public_id, 'assignee_type' => $data['assignee_type'], 'assignee_id' => $data['assignee_id']];
    }

    public function createAgent(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_agents', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'role_key' => $this->required($data, 'role_key'),
            'description' => $data['description'] ?? null, 'system_instruction' => $data['system_instruction'] ?? null,
            'autonomy_level' => (string) ($data['autonomy_level'] ?? 'assist'),
            'routing_policy_public_id' => $data['routing_policy_public_id'] ?? null,
            'memory_policy' => $this->json($data['memory_policy'] ?? null), 'status' => 'draft', 'created_by_user_id' => $actorId,
        ]);
    }

    public function assignAgentTool(array $data, int $companyId, int $actorId): array
    {
        $agent = $this->byPublicId('titan_agents', $companyId, $this->required($data, 'agent_public_id'));
        $this->db->table('titan_agent_tools')->updateOrInsert([
            'company_id' => $companyId, 'agent_id' => $agent->id, 'tool_id' => $this->required($data, 'tool_id'),
        ], [
            'enabled' => (bool) ($data['enabled'] ?? true),
            'requires_confirmation' => (bool) ($data['requires_confirmation'] ?? false),
            'constraints' => $this->json($data['constraints'] ?? null), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return ['agent_public_id' => $agent->public_id, 'tool_id' => $data['tool_id']];
    }

    public function createConnection(array $data, int $companyId, int $actorId): array
    {
        foreach (['credential_reference'] as $required) $this->required($data, $required);
        return $this->insertAggregate('titan_connections', $data, $companyId, $actorId, [
            'connector_key' => $this->required($data, 'connector_key'), 'name' => $this->required($data, 'name'),
            'credential_reference' => $data['credential_reference'], 'token_reference' => $data['token_reference'] ?? null,
            'scopes' => $this->json($data['scopes'] ?? null), 'configuration' => $this->json($data['configuration'] ?? null),
            'status' => 'pending', 'created_by_user_id' => $actorId,
        ]);
    }


    public function createProviderConnection(array $data, int $companyId, int $actorId): array
    {
        foreach (['credential_reference'] as $required) $this->required($data, $required);
        return $this->insertAggregate('titan_provider_connections', $data, $companyId, $actorId, [
            'provider_key' => $this->required($data, 'provider_key'), 'name' => $this->required($data, 'name'),
            'credential_reference' => $data['credential_reference'], 'secret_reference' => $data['secret_reference'] ?? null,
            'endpoint' => $data['endpoint'] ?? null, 'api_version' => $data['api_version'] ?? null,
            'capabilities' => $this->json($data['capabilities'] ?? null), 'configuration' => $this->json($data['configuration'] ?? null),
            'status' => 'pending', 'created_by_user_id' => $actorId,
        ]);
    }

    public function upsertModel(array $data, int $companyId, int $actorId): array
    {
        $provider = $this->byPublicId('titan_provider_connections', $companyId, $this->required($data, 'provider_public_id'));
        $publicId = (string) ($data['public_id'] ?? Str::ulid());
        $this->db->table('titan_model_catalogue')->updateOrInsert([
            'company_id' => $companyId, 'provider_connection_id' => $provider->id, 'model_key' => $this->required($data, 'model_key'),
        ], [
            'public_id' => $publicId, 'label' => $this->required($data, 'label'), 'capabilities' => $this->json($data['capabilities'] ?? []),
            'context_window' => $data['context_window'] ?? null, 'input_cost_minor_per_million' => $data['input_cost_minor_per_million'] ?? null,
            'output_cost_minor_per_million' => $data['output_cost_minor_per_million'] ?? null, 'latency_ms' => $data['latency_ms'] ?? null,
            'quality_score' => $data['quality_score'] ?? null, 'status' => (string) ($data['status'] ?? 'active'),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('titan_model_catalogue', $companyId, $publicId);
    }

    public function createRoutingPolicy(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_routing_policies', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'purpose' => $this->required($data, 'purpose'),
            'required_capabilities' => $this->json($data['required_capabilities'] ?? null),
            'max_input_cost_minor_per_million' => $data['max_input_cost_minor_per_million'] ?? null,
            'max_output_cost_minor_per_million' => $data['max_output_cost_minor_per_million'] ?? null,
            'max_latency_ms' => $data['max_latency_ms'] ?? null, 'strategy' => (string) ($data['strategy'] ?? 'balanced'),
            'fallback_model_public_id' => $data['fallback_model_public_id'] ?? null, 'status' => 'active', 'created_by_user_id' => $actorId,
        ]);
    }

    public function startCouncilRun(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_model_council_runs', $data, $companyId, $actorId, [
            'conversation_id' => $data['conversation_id'] ?? null, 'routing_policy_id' => $data['routing_policy_id'] ?? null,
            'prompt_hash' => hash('sha256', $this->required($data, 'prompt')), 'status' => 'queued',
            'created_by_user_id' => $actorId,
        ]);
    }

    public function createVoiceProfile(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_voice_profiles', $data, $companyId, $actorId, [
            'name' => $this->required($data, 'name'), 'provider_key' => $this->required($data, 'provider_key'),
            'voice_key' => $this->required($data, 'voice_key'), 'credential_reference' => $data['credential_reference'] ?? null,
            'locale' => (string) ($data['locale'] ?? 'en-AU'), 'input_modalities' => $this->json($data['input_modalities'] ?? null),
            'output_modalities' => $this->json($data['output_modalities'] ?? null), 'status' => 'active',
            'configuration' => $this->json($data['configuration'] ?? null), 'created_by_user_id' => $actorId,
        ]);
    }

    public function startVoiceSession(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_voice_sessions', $data, $companyId, $actorId, [
            'conversation_id' => $data['conversation_id'] ?? null, 'agent_id' => $data['agent_id'] ?? null,
            'voice_profile_id' => $data['voice_profile_id'] ?? null, 'direction' => (string) ($data['direction'] ?? 'in_app'),
            'status' => 'created', 'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
        ]);
    }

    public function publishAnnouncement(array $data, int $companyId, int $actorId): array
    {
        return $this->insertAggregate('titan_announcements', $data, $companyId, $actorId, [
            'title' => $this->required($data, 'title'), 'body' => $this->required($data, 'body'),
            'audience_type' => (string) ($data['audience_type'] ?? 'company'), 'audience_filter' => $this->json($data['audience_filter'] ?? null),
            'status' => (string) ($data['status'] ?? 'published'), 'publish_at' => $data['publish_at'] ?? now(),
            'expires_at' => $data['expires_at'] ?? null, 'created_by_user_id' => $actorId,
        ]);
    }


    public function createOnboardingFlow(array $data, int $companyId, int $actorId): array
    {
        return $this->db->transaction(function () use ($data, $companyId, $actorId): array {
            $flow = $this->insertAggregate('titan_onboarding_flows', $data, $companyId, $actorId, [
                'flow_key' => Str::slug($this->required($data, 'flow_key')), 'name' => $this->required($data, 'name'),
                'description' => $data['description'] ?? null, 'version' => max(1, (int) ($data['version'] ?? 1)),
                'status' => 'active', 'created_by_user_id' => $actorId,
            ]);
            foreach ((array) ($data['steps'] ?? []) as $index => $step) {
                if (! is_array($step)) continue;
                $this->db->table('titan_onboarding_steps')->insert([
                    'company_id' => $companyId, 'flow_id' => $flow['id'],
                    'step_key' => Str::slug($this->required($step, 'step_key')), 'title' => $this->required($step, 'title'),
                    'description' => $step['description'] ?? null, 'position' => max(0, (int) ($step['position'] ?? $index)),
                    'required' => (bool) ($step['required'] ?? true), 'completion_rule' => $this->json($step['completion_rule'] ?? null),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return $flow;
        }, 3);
    }

    public function recordOnboardingProgress(array $data, int $companyId, int $actorId): array
    {
        $this->db->table('titan_onboarding_progress')->updateOrInsert([
            'company_id' => $companyId, 'flow_id' => (int) $this->required($data, 'flow_id'),
            'step_id' => (int) $this->required($data, 'step_id'), 'user_id' => (int) ($data['user_id'] ?? $actorId),
        ], [
            'status' => (string) ($data['status'] ?? 'completed'),
            'completed_at' => ($data['status'] ?? 'completed') === 'completed' ? now() : null,
            'metadata' => $this->json($data['metadata'] ?? null), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return ['flow_id' => (int) $data['flow_id'], 'step_id' => (int) $data['step_id'], 'status' => (string) ($data['status'] ?? 'completed')];
    }

    public function summary(int $companyId): array
    {
        if ($companyId < 1) throw new InvalidArgumentException('Company context is required.');
        $tables = [
            'folders' => 'titan_workspace_folders', 'canvases' => 'titan_workspace_canvases', 'memories' => 'titan_memories',
            'skills' => 'titan_skills', 'agents' => 'titan_agents', 'connections' => 'titan_connections',
            'providers' => 'titan_provider_connections', 'models' => 'titan_model_catalogue', 'voice_sessions' => 'titan_voice_sessions',
            'announcements' => 'titan_announcements', 'onboarding_pending' => 'titan_onboarding_progress',
        ];
        $summary = [];
        foreach ($tables as $key => $table) {
            if (! $this->db->getSchemaBuilder()->hasTable($table)) {
                $summary[$key] = null;
                continue;
            }

            $query = $this->db->table($table)->where('company_id', $companyId);
            if ($key === 'onboarding_pending') {
                $query->where('status', '!=', 'completed');
            }
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
        if ($record === null) throw new \RuntimeException('Intelligence record was not found.');
        return (array) $record;
    }

    private function byPublicId(string $table, int $companyId, string $publicId): object
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->first();
        if ($record === null) throw new InvalidArgumentException('Referenced Titan intelligence record was not found.');
        return $record;
    }

    private function guard(int $companyId, int $actorId): void
    {
        if ($companyId < 1 || $actorId < 1) throw new InvalidArgumentException('Active company and actor are required.');
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
