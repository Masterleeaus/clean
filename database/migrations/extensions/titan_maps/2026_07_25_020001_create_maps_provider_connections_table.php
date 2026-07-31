<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maps_provider_connections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->string('provider', 80);
            $table->string('credential_reference', 500);
            $table->boolean('enabled')->default(false);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->json('configuration')->nullable();
            $table->json('capabilities')->nullable();
            $table->string('terms_version')->nullable();
            $table->timestampTz('last_validated_at')->nullable();
            $table->timestampsTz();
            $table->unique(['company_id', 'provider']);
        });
    }
    public function down(): void { Schema::dropIfExists('maps_provider_connections'); }
};
