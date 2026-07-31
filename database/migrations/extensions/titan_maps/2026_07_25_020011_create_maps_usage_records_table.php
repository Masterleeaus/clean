<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maps_usage_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->string('provider', 80)->index();
            $table->string('operation', 80)->index();
            $table->unsignedInteger('request_count')->default(1);
            $table->unsignedInteger('result_count')->default(0);
            $table->decimal('billable_units', 12, 4)->default(1.0);
            $table->decimal('estimated_cost', 12, 6)->nullable();
            $table->string('currency', 3)->nullable();
            $table->uuid('discovery_search_id')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->string('agent_id')->nullable()->index();
            $table->timestampTz('recorded_at');
            $table->timestampsTz();
            $table->index(['company_id', 'provider', 'operation']);
        });
    }
    public function down(): void { Schema::dropIfExists('maps_usage_records'); }
};
