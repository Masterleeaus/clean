<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titan_brand_kits', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->text('description')->nullable(); $table->json('colours')->nullable(); $table->json('typography')->nullable(); $table->json('voice')->nullable(); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_brand_assets', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('brand_kit_id')->index(); $table->string('asset_type', 64); $table->string('name'); $table->string('storage_reference'); $table->string('mime_type')->nullable(); $table->string('checksum_sha256', 64)->nullable(); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_audiences', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->text('description')->nullable(); $table->json('traits')->nullable(); $table->json('channels')->nullable(); $table->string('entity_type', 80)->nullable(); $table->string('entity_public_id', 64)->nullable(); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_content_templates', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('content_type', 64); $table->longText('template_body')->nullable(); $table->json('variables')->nullable(); $table->unsignedInteger('version')->default(1); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_creative_projects', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('project_type', 64); $table->unsignedBigInteger('brand_kit_id')->nullable()->index(); $table->unsignedBigInteger('campaign_id')->nullable()->index(); $table->text('brief')->nullable(); $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('owner_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_creative_assets', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('project_id')->nullable()->index(); $table->string('asset_type', 64); $table->string('name'); $table->string('storage_reference'); $table->string('mime_type')->nullable(); $table->unsignedBigInteger('size_bytes')->nullable(); $table->string('checksum_sha256', 64)->nullable(); $table->string('source_type', 32)->default('uploaded'); $table->string('status', 32)->default('active'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_creative_revisions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('asset_id')->index(); $table->unsignedInteger('revision'); $table->string('storage_reference'); $table->string('checksum_sha256', 64)->nullable(); $table->text('change_summary')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamp('created_at')->useCurrent(); $table->unique(['company_id','asset_id','revision']);
        });
        Schema::create('titan_generation_jobs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('project_id')->nullable()->index(); $table->string('job_type', 64); $table->string('provider_connection_public_id', 32)->nullable(); $table->string('model_key', 120)->nullable(); $table->string('prompt_hash', 64)->nullable(); $table->json('parameters')->nullable(); $table->string('status', 32)->default('queued'); $table->string('error_code')->nullable(); $table->unsignedBigInteger('estimated_cost_minor')->nullable(); $table->unsignedBigInteger('actual_cost_minor')->nullable(); $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_generation_outputs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('generation_job_id')->index(); $table->string('output_type', 64); $table->string('storage_reference')->nullable(); $table->longText('text_output')->nullable(); $table->string('checksum_sha256', 64)->nullable(); $table->json('provider_metadata')->nullable(); $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('titan_campaigns', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->unsignedBigInteger('brand_kit_id')->nullable()->index(); $table->unsignedBigInteger('audience_id')->nullable()->index(); $table->string('campaign_type', 64); $table->date('start_date')->nullable(); $table->date('end_date')->nullable(); $table->unsignedBigInteger('budget_minor')->nullable(); $table->string('currency', 3)->default('AUD'); $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('owner_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_campaign_objectives', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('campaign_id')->index(); $table->string('objective_type', 64); $table->string('metric_key', 80)->nullable(); $table->decimal('target_value', 18, 4)->nullable(); $table->string('unit', 32)->nullable(); $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('titan_content_items', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('campaign_id')->nullable()->index(); $table->unsignedBigInteger('project_id')->nullable()->index(); $table->unsignedBigInteger('template_id')->nullable()->index(); $table->string('content_type', 64); $table->string('title')->nullable(); $table->longText('body')->nullable(); $table->json('asset_public_ids')->nullable(); $table->boolean('approval_required')->default(true); $table->string('approval_status', 32)->default('pending'); $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_content_calendar_entries', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('content_item_id')->index(); $table->timestamp('scheduled_at'); $table->string('timezone', 64)->default('Australia/Sydney'); $table->string('status', 32)->default('scheduled'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','scheduled_at']);
        });
        Schema::create('titan_publication_channels', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('channel_type', 64); $table->string('connection_public_id', 32)->nullable(); $table->string('external_account_reference')->nullable(); $table->string('status', 32)->default('disabled'); $table->json('configuration')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_publications', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('content_item_id')->index(); $table->unsignedBigInteger('publication_channel_id')->index(); $table->unsignedBigInteger('calendar_entry_id')->nullable()->index(); $table->string('status', 32)->default('draft'); $table->string('provider_reference')->nullable(); $table->string('permalink')->nullable(); $table->timestamp('scheduled_at')->nullable(); $table->timestamp('published_at')->nullable(); $table->timestamp('retracted_at')->nullable(); $table->string('error_code')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']); $table->index(['company_id','status']);
        });
        Schema::create('titan_marketing_automations', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->string('name'); $table->string('trigger_type', 64); $table->json('trigger_configuration')->nullable(); $table->json('actions')->nullable(); $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_marketing_automation_runs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('automation_id')->index(); $table->string('trigger_reference')->nullable(); $table->string('status', 32)->default('queued'); $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable(); $table->string('error_code')->nullable(); $table->json('result')->nullable(); $table->timestamp('created_at')->useCurrent(); $table->index(['company_id','status']);
        });
        Schema::create('titan_seo_briefs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('campaign_id')->nullable()->index(); $table->string('topic'); $table->string('primary_keyword')->nullable(); $table->json('secondary_keywords')->nullable(); $table->string('search_intent', 64)->nullable(); $table->text('outline')->nullable(); $table->json('source_references')->nullable(); $table->string('status', 32)->default('draft'); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_newsletters', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('public_id', 32); $table->unsignedBigInteger('campaign_id')->nullable()->index(); $table->unsignedBigInteger('audience_id')->nullable()->index(); $table->string('subject'); $table->string('preview_text')->nullable(); $table->longText('body')->nullable(); $table->string('status', 32)->default('draft'); $table->timestamp('scheduled_at')->nullable(); $table->unsignedBigInteger('created_by_user_id')->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->unique(['company_id','public_id']);
        });
        Schema::create('titan_analytics_observations', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->string('subject_type', 80); $table->string('subject_public_id', 64); $table->string('metric_key', 80); $table->decimal('metric_value', 20, 6); $table->string('unit', 32)->nullable(); $table->string('source_type', 64); $table->string('source_reference')->nullable(); $table->timestamp('observed_at'); $table->json('dimensions')->nullable(); $table->timestamp('created_at')->useCurrent(); $table->index(['company_id','subject_type','subject_public_id']); $table->index(['company_id','metric_key','observed_at']);
        });
        Schema::create('titan_content_approvals', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->index(); $table->unsignedBigInteger('content_item_id')->index(); $table->string('decision', 32); $table->text('comment')->nullable(); $table->unsignedBigInteger('decided_by_user_id')->index(); $table->timestamp('decided_at'); $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (array_reverse([
            'titan_brand_kits','titan_brand_assets','titan_audiences','titan_content_templates','titan_creative_projects','titan_creative_assets','titan_creative_revisions','titan_generation_jobs','titan_generation_outputs','titan_campaigns','titan_campaign_objectives','titan_content_items','titan_content_calendar_entries','titan_publication_channels','titan_publications','titan_marketing_automations','titan_marketing_automation_runs','titan_seo_briefs','titan_newsletters','titan_analytics_observations','titan_content_approvals',
        ]) as $table) Schema::dropIfExists($table);
    }
};
