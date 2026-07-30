<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_place_contacts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->uuid('external_place_id')->index();
            $table->string('contact_type', 40)->index();
            $table->text('normalised_value');
            $table->text('display_value');
            $table->string('source_provider', 80);
            $table->text('source_url')->nullable();
            $table->string('verification_status', 40)->default('unverified');
            $table->string('verification_method')->nullable();
            $table->decimal('confidence', 5, 4)->default(0.5);
            $table->timestampTz('first_observed_at');
            $table->timestampTz('last_observed_at');
            $table->timestampsTz();
            $table->index(['company_id', 'external_place_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('external_place_contacts'); }
};
