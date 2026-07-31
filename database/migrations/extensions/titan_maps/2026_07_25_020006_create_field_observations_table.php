<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_observations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('external_place_id')->index();
            $table->string('field', 120)->index();
            $table->json('observed_value')->nullable();
            $table->string('provider', 80)->index();
            $table->text('source_url')->nullable();
            $table->timestampTz('observed_at');
            $table->decimal('confidence', 5, 4)->default(0.5);
            $table->timestampTz('expires_at')->nullable();
            $table->json('restrictions')->nullable();
            $table->string('observation_type', 40)->default('provider_observed');
            $table->timestampsTz();
            $table->index(['company_id', 'external_place_id', 'field']);
        });
    }
    public function down(): void { Schema::dropIfExists('field_observations'); }
};
