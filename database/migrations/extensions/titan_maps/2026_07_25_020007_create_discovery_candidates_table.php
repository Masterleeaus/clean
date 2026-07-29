<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discovery_candidates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('discovery_search_id')->nullable()->index();
            $table->uuid('external_place_id')->index();
            $table->string('candidate_type', 80)->default('unclassified')->index();
            $table->string('lifecycle_status', 40)->default('new')->index();
            $table->decimal('relevance_score', 5, 4)->default(0.0);
            $table->decimal('confidence_score', 5, 4)->default(0.0);
            $table->json('classification_evidence')->nullable();
            $table->string('assigned_user_id')->nullable()->index();
            $table->string('assigned_agent_id')->nullable()->index();
            $table->string('existing_workcore_entity_type')->nullable();
            $table->string('existing_workcore_entity_id')->nullable();
            $table->string('review_status', 40)->default('pending')->index();
            $table->boolean('unresolved_match')->default(false);
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestampsTz();
            $table->unique(['company_id', 'discovery_search_id', 'external_place_id', 'candidate_type'], 'candidate_identity_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('discovery_candidates'); }
};
