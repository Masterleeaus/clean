<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titan_workspace_folders', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('owner_user_id')->index(); $table->string('name'); $table->text('description')->nullable(); $table->unsignedInteger('position')->default(0);
            $table->string('status', 32)->default('active'); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_workspace_canvases', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('folder_id')->nullable()->index();
            $table->unsignedBigInteger('conversation_id')->nullable()->index(); $table->string('title'); $table->longText('content')->nullable(); $table->string('content_format', 32)->default('tiptap-json');
            $table->unsignedInteger('version')->default(1); $table->boolean('temporary')->default(false); $table->string('status', 32)->default('active');
            $table->unsignedBigInteger('created_by_user_id')->index(); $table->unsignedBigInteger('updated_by_user_id')->nullable()->index(); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_workspace_shares', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('subject_type', 64); $table->string('subject_public_id', 32);
            $table->string('token_hash', 64); $table->string('permission', 32)->default('view'); $table->timestamp('expires_at')->nullable(); $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->unique(['company_id', 'token_hash']);
        });
        Schema::create('titan_memories', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('subject_type', 64); $table->string('subject_id', 64)->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable()->index(); $table->string('agent_public_id', 32)->nullable()->index(); $table->string('memory_type', 48); $table->string('scope', 48);
            $table->string('title')->nullable(); $table->longText('content'); $table->string('source_type', 64); $table->string('source_reference')->nullable(); $table->decimal('confidence', 5, 4)->default(1);
            $table->string('sensitivity', 32)->default('normal'); $table->unsignedInteger('retention_days')->nullable(); $table->timestamp('expires_at')->nullable(); $table->boolean('promoted_from_temporary')->default(false);
            $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']); $table->index(['company_id', 'subject_type', 'subject_id']);
        });
        Schema::create('titan_skills', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('slug'); $table->string('name'); $table->text('description')->nullable();
            $table->string('visibility', 32)->default('company'); $table->string('status', 32)->default('draft'); $table->unsignedInteger('current_version')->default(1); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->unique(['company_id', 'slug']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_skill_versions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('skill_id')->index(); $table->unsignedInteger('version'); $table->longText('instructions');
            $table->json('input_schema')->nullable(); $table->json('output_schema')->nullable(); $table->json('provenance')->nullable(); $table->string('checksum', 64); $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps(); $table->unique(['company_id', 'skill_id', 'version']);
        });
        Schema::create('titan_skill_assignments', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('skill_id')->index(); $table->string('assignee_type', 32); $table->string('assignee_id', 64);
            $table->boolean('enabled')->default(true); $table->json('configuration')->nullable(); $table->unsignedBigInteger('assigned_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'skill_id', 'assignee_type', 'assignee_id']);
        });
        Schema::create('titan_agents', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('role_key', 80); $table->text('description')->nullable();
            $table->longText('system_instruction')->nullable(); $table->string('autonomy_level', 32)->default('assist'); $table->string('routing_policy_public_id', 32)->nullable(); $table->json('memory_policy')->nullable();
            $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_agent_tools', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('agent_id')->index(); $table->string('tool_id'); $table->boolean('enabled')->default(true);
            $table->boolean('requires_confirmation')->default(false); $table->json('constraints')->nullable(); $table->timestamps(); $table->unique(['company_id', 'agent_id', 'tool_id']);
        });
        Schema::create('titan_agent_knowledge_sources', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('agent_id')->index(); $table->string('source_type', 64); $table->string('source_public_id', 64);
            $table->string('label')->nullable(); $table->string('sync_policy', 32)->default('manual'); $table->string('status', 32)->default('active'); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'agent_id', 'source_type', 'source_public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_agent_channels', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('agent_id')->index(); $table->string('connection_public_id', 32)->nullable(); $table->string('channel_type', 64);
            $table->string('external_identity')->nullable(); $table->string('status', 32)->default('inactive'); $table->json('configuration')->nullable(); $table->timestamp('last_seen_at')->nullable(); $table->timestamps();
            $table->index(['company_id', 'status']);
        });
        Schema::create('titan_agent_workflows', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('agent_id')->index(); $table->string('name'); $table->string('trigger_type', 64);
            $table->json('definition'); $table->unsignedInteger('version')->default(1); $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_agent_runs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('agent_id')->index(); $table->unsignedBigInteger('workflow_id')->nullable()->index();
            $table->string('trigger_type', 64); $table->string('status', 32)->default('queued'); $table->json('input')->nullable(); $table->json('output')->nullable(); $table->string('error_code')->nullable();
            $table->string('correlation_id', 64)->nullable()->index(); $table->timestamp('started_at')->nullable(); $table->timestamp('finished_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_connections', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('connector_key', 80); $table->string('name');
            $table->string('credential_reference'); $table->string('token_reference')->nullable(); $table->json('scopes')->nullable(); $table->json('configuration')->nullable(); $table->string('status', 32)->default('pending');
            $table->string('error_code')->nullable(); $table->timestamp('last_validated_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']); $table->index(['company_id', 'connector_key']);
        });
        Schema::create('titan_connection_sync_states', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('connection_id')->index(); $table->string('stream_key', 100); $table->text('cursor')->nullable();
            $table->timestamp('watermark_at')->nullable(); $table->string('status', 32)->default('idle'); $table->timestamp('last_synced_at')->nullable(); $table->timestamp('next_sync_at')->nullable(); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'connection_id', 'stream_key']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_provider_connections', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('provider_key', 80); $table->string('name'); $table->string('credential_reference');
            $table->string('secret_reference')->nullable(); $table->string('endpoint')->nullable(); $table->string('api_version')->nullable(); $table->json('capabilities')->nullable(); $table->json('configuration')->nullable();
            $table->string('status', 32)->default('pending'); $table->timestamp('last_validated_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']); $table->index(['company_id', 'provider_key']);
        });
        Schema::create('titan_model_catalogue', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('provider_connection_id')->index(); $table->string('model_key', 120); $table->string('label');
            $table->json('capabilities'); $table->unsignedInteger('context_window')->nullable(); $table->unsignedBigInteger('input_cost_minor_per_million')->nullable(); $table->unsignedBigInteger('output_cost_minor_per_million')->nullable();
            $table->unsignedInteger('latency_ms')->nullable(); $table->decimal('quality_score', 5, 4)->nullable(); $table->string('status', 32)->default('active'); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->unique(['company_id', 'provider_connection_id', 'model_key']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_routing_policies', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('purpose', 80); $table->json('required_capabilities')->nullable();
            $table->unsignedBigInteger('max_input_cost_minor_per_million')->nullable(); $table->unsignedBigInteger('max_output_cost_minor_per_million')->nullable(); $table->unsignedInteger('max_latency_ms')->nullable();
            $table->string('strategy', 32)->default('balanced'); $table->string('fallback_model_public_id', 32)->nullable(); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_model_council_runs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('conversation_id')->nullable()->index(); $table->unsignedBigInteger('routing_policy_id')->nullable()->index();
            $table->string('prompt_hash', 64); $table->string('status', 32)->default('queued'); $table->unsignedBigInteger('selected_response_id')->nullable(); $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_model_council_responses', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('council_run_id')->index(); $table->string('provider_key', 80); $table->string('model_key', 120);
            $table->longText('response_text')->nullable(); $table->string('response_hash', 64)->nullable(); $table->unsignedInteger('latency_ms')->nullable(); $table->unsignedInteger('input_tokens')->nullable(); $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedBigInteger('estimated_cost_minor')->nullable(); $table->decimal('quality_score', 5, 4)->nullable(); $table->boolean('selected')->default(false); $table->string('error_code')->nullable(); $table->timestamp('created_at')->useCurrent();
           
        });
        Schema::create('titan_voice_profiles', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('provider_key', 80); $table->string('voice_key', 120);
            $table->string('credential_reference')->nullable(); $table->string('locale', 16)->default('en-AU'); $table->json('input_modalities')->nullable(); $table->json('output_modalities')->nullable();
            $table->string('status', 32)->default('active'); $table->json('configuration')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_voice_sessions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('conversation_id')->nullable()->index(); $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->unsignedBigInteger('voice_profile_id')->nullable()->index(); $table->string('provider_session_reference')->nullable(); $table->string('direction', 16)->default('in_app'); $table->string('status', 32)->default('created');
            $table->timestamp('started_at')->nullable(); $table->timestamp('ended_at')->nullable(); $table->unsignedInteger('duration_seconds')->nullable(); $table->string('error_code')->nullable(); $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps(); $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_voice_transcripts', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('voice_session_id')->index(); $table->string('speaker', 32); $table->unsignedInteger('sequence'); $table->longText('content');
            $table->decimal('confidence', 5, 4)->nullable(); $table->unsignedInteger('started_ms')->nullable(); $table->unsignedInteger('ended_ms')->nullable(); $table->timestamp('created_at')->useCurrent();
            $table->unique(['company_id', 'voice_session_id', 'sequence']);
        });
        Schema::create('titan_voice_events', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('voice_session_id')->index(); $table->string('event_type', 80); $table->string('provider_event_reference')->nullable();
            $table->json('payload')->nullable(); $table->timestamp('occurred_at'); $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('titan_announcements', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('title'); $table->longText('body'); $table->string('audience_type', 32)->default('company');
            $table->json('audience_filter')->nullable(); $table->string('status', 32)->default('draft'); $table->timestamp('publish_at')->nullable(); $table->timestamp('expires_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_announcement_receipts', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('announcement_id')->index(); $table->unsignedBigInteger('user_id')->index(); $table->timestamp('seen_at')->nullable(); $table->timestamp('dismissed_at')->nullable(); $table->timestamp('created_at')->useCurrent();
            $table->unique(['company_id', 'announcement_id', 'user_id']);
        });
        Schema::create('titan_onboarding_flows', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('flow_key', 80); $table->string('name'); $table->text('description')->nullable();
            $table->unsignedInteger('version')->default(1); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps();
            $table->unique(['company_id', 'public_id']); $table->unique(['company_id', 'flow_key']); $table->index(['company_id', 'status']);
        });
        Schema::create('titan_onboarding_steps', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('flow_id')->index(); $table->string('step_key', 80); $table->string('title'); $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0); $table->boolean('required')->default(true); $table->json('completion_rule')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'flow_id', 'step_key']);
        });
        Schema::create('titan_onboarding_progress', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('flow_id')->index(); $table->unsignedBigInteger('step_id')->index(); $table->unsignedBigInteger('user_id')->index();
            $table->string('status', 32)->default('pending'); $table->timestamp('completed_at')->nullable(); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'flow_id', 'step_id', 'user_id']); $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        foreach (array_reverse([
            'titan_workspace_folders','titan_workspace_canvases','titan_workspace_shares','titan_memories','titan_skills','titan_skill_versions','titan_skill_assignments',
            'titan_agents','titan_agent_tools','titan_agent_knowledge_sources','titan_agent_channels','titan_agent_workflows','titan_agent_runs','titan_connections','titan_connection_sync_states',
            'titan_provider_connections','titan_model_catalogue','titan_routing_policies','titan_model_council_runs','titan_model_council_responses','titan_voice_profiles','titan_voice_sessions',
            'titan_voice_transcripts','titan_voice_events','titan_announcements','titan_announcement_receipts','titan_onboarding_flows','titan_onboarding_steps','titan_onboarding_progress',
        ]) as $table) Schema::dropIfExists($table);
    }
};
