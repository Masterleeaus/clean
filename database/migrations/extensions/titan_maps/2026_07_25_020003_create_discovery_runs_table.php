<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discovery_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('discovery_search_id')->index();
            $table->string('provider', 80)->index();
            $table->text('cursor')->nullable();
            $table->string('current_stage', 80)->default('queued');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedInteger('results_discovered')->default(0);
            $table->unsignedInteger('results_processed')->default(0);
            $table->unsignedInteger('results_skipped')->default(0);
            $table->unsignedInteger('duplicates_detected')->default(0);
            $table->unsignedInteger('failures')->default(0);
            $table->json('rate_limit_state')->nullable();
            $table->timestampTz('retry_after')->nullable();
            $table->json('checkpoint')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('safe_failure_details')->nullable();
            $table->timestampsTz();
            $table->unique(['company_id', 'discovery_search_id', 'provider'], 'discovery_run_provider_unique');
            $table->index(['company_id', 'discovery_search_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('discovery_runs'); }
};
