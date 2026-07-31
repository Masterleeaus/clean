<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_places', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->string('provider', 80)->index();
            $table->string('provider_place_id', 255);
            $table->string('canonical_key', 512)->index();
            $table->string('name', 500);
            $table->string('legal_name', 500)->nullable();
            $table->text('description')->nullable();
            $table->json('categories')->nullable();
            $table->string('primary_category')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('suburb')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('postcode', 20)->nullable()->index();
            $table->string('country', 3)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->string('phone', 100)->nullable()->index();
            $table->text('website')->nullable();
            $table->string('public_email', 320)->nullable()->index();
            $table->json('opening_hours')->nullable();
            $table->boolean('currently_open')->nullable();
            $table->boolean('temporarily_closed')->default(false);
            $table->boolean('permanently_closed')->default(false);
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->string('price_level')->nullable();
            $table->text('source_url')->nullable();
            $table->string('raw_payload_reference', 500)->nullable();
            $table->string('raw_payload_hash', 64)->nullable();
            $table->timestampTz('first_observed_at');
            $table->timestampTz('last_observed_at');
            $table->timestampTz('last_verified_at')->nullable();
            $table->decimal('confidence', 5, 4)->default(0.5);
            $table->string('data_freshness', 40)->default('observed');
            $table->json('provider_terms_metadata')->nullable();
            $table->timestampsTz();
            $table->unique(['company_id', 'provider', 'provider_place_id'], 'external_places_provider_identity_unique');
            $table->index(['company_id', 'canonical_key']);
        });
    }
    public function down(): void { Schema::dropIfExists('external_places'); }
};
