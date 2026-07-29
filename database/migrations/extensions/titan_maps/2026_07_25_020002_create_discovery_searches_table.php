<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discovery_searches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->string('requested_by_user_id')->index();
            $table->string('conversation_id')->nullable()->index();
            $table->string('purpose', 80)->index();
            $table->text('query');
            $table->string('category')->nullable()->index();
            $table->json('categories')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_metres')->nullable();
            $table->decimal('north_boundary', 10, 7)->nullable();
            $table->decimal('south_boundary', 10, 7)->nullable();
            $table->decimal('east_boundary', 10, 7)->nullable();
            $table->decimal('west_boundary', 10, 7)->nullable();
            $table->string('language', 12)->default('en');
            $table->string('country', 3)->nullable();
            $table->json('filters')->nullable();
            $table->json('provider_strategy')->nullable();
            $table->unsignedInteger('maximum_results')->default(20);
            $table->string('status', 40)->index();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();
            $table->index(['company_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('discovery_searches'); }
};
