<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('territory_analyses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('discovery_search_id')->nullable()->index();
            $table->json('search_area');
            $table->json('categories')->nullable();
            $table->string('analysis_type', 80)->index();
            $table->json('observation_period')->nullable();
            $table->json('result_summary');
            $table->string('map_layer_reference', 500)->nullable();
            $table->json('generated_metrics');
            $table->json('source_coverage')->nullable();
            $table->decimal('confidence', 5, 4)->default(0.0);
            $table->timestampTz('generated_at');
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('territory_analyses'); }
};
