<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titan_vault_secrets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('provider', 96);
            $table->string('secret_key', 160);
            $table->longText('encrypted_value');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'provider', 'secret_key'], 'titan_vault_lookup');
        });

        Schema::create('titan_audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->nullable()->constrained('tz_companies')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('agent_id', 160)->nullable();
            $table->string('conversation_id', 160)->nullable();
            $table->string('action', 190)->index();
            $table->string('entity_type', 190)->nullable();
            $table->string('entity_id', 190)->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->text('reason')->nullable();
            $table->uuid('correlation_id')->nullable()->index();
            $table->uuid('causation_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['company_id', 'entity_type', 'entity_id'], 'titan_audit_entity_lookup');
        });

        Schema::create('titan_extensions', function (Blueprint $table): void {
            $table->string('id', 120)->primary();
            $table->string('name');
            $table->string('version', 40);
            $table->string('type', 40);
            $table->string('provider');
            $table->boolean('enabled')->default(false)->index();
            $table->boolean('compatible')->default(false);
            $table->string('status', 40)->default('discovered')->index();
            $table->string('manifest_hash', 64);
            $table->text('last_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('discovered_at')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('titan_capabilities', function (Blueprint $table): void {
            $table->string('id', 190)->primary();
            $table->string('provider', 120);
            $table->string('version', 40)->default('1.0.0');
            $table->boolean('enabled')->default(true)->index();
            $table->json('definition');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_capabilities');
        Schema::dropIfExists('titan_extensions');
        Schema::dropIfExists('titan_audit_logs');
        Schema::dropIfExists('titan_vault_secrets');
    }
};
