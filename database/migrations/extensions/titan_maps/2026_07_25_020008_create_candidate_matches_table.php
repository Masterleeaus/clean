<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_matches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('candidate_id')->index();
            $table->string('workcore_entity_type', 80);
            $table->string('workcore_entity_id')->index();
            $table->decimal('match_score', 5, 4);
            $table->json('matching_fields')->nullable();
            $table->json('conflicting_fields')->nullable();
            $table->string('match_strategy', 80);
            $table->string('human_review_status', 40)->default('pending');
            $table->string('resolution', 40)->nullable();
            $table->timestampsTz();
            $table->index(['company_id', 'candidate_id', 'match_score']);
        });
    }
    public function down(): void { Schema::dropIfExists('candidate_matches'); }
};
