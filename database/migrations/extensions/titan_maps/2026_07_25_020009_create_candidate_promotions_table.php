<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_promotions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('candidate_id')->index();
            $table->string('target_workcore_service', 160)->nullable();
            $table->string('target_entity_type', 80);
            $table->string('target_entity_id')->nullable()->index();
            $table->string('requested_by_user_id')->index();
            $table->string('approved_by_user_id')->nullable()->index();
            $table->string('agent_id')->nullable()->index();
            $table->string('conversation_id')->nullable()->index();
            $table->json('accepted_fields');
            $table->json('rejected_fields')->nullable();
            $table->text('reason')->nullable();
            $table->string('workcore_command_id')->nullable()->index();
            $table->string('workcore_event_id')->nullable()->index();
            $table->timestampTz('promoted_at')->nullable();
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('candidate_promotions'); }
};
