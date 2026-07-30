<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tt_competencies', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->string('code', 80);
                    $table->string('name');
                    $table->text('description')->nullable();
                    $table->unsignedInteger('validity_days')->nullable();
                    $table->string('assessment_kind', 32)->nullable();
                    $table->boolean('active')->default(true);
                    $table->timestamps();
                    $table->unique(['company_id', 'code'], 'tt_competency_company_code_uq');
                });

        Schema::create('tt_competency_grants', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('competency_id')->constrained('tt_competencies')->restrictOnDelete();
                    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                    $table->foreignId('assignment_id')->nullable()->constrained('tt_assignments')->nullOnDelete();
                    $table->foreignId('attempt_id')->nullable()->constrained('tt_attempts')->nullOnDelete();
                    $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('status', 24)->default('active');
                    $table->timestamp('granted_at');
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamp('revoked_at')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->unique(['company_id', 'competency_id', 'user_id', 'assignment_id'], 'tt_grant_assignment_uq');
                    $table->index(['company_id', 'user_id', 'status'], 'tt_grant_user_status_ix');
                });

        Schema::create('tt_certificates', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                    $table->foreignId('program_id')->constrained('tt_programs')->restrictOnDelete();
                    $table->foreignId('assignment_id')->nullable()->constrained('tt_assignments')->nullOnDelete();
                    $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('certificate_number', 80);
                    $table->string('verification_code', 96)->unique();
                    $table->string('status', 24)->default('active');
                    $table->timestamp('issued_at');
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamp('revoked_at')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->unique(['company_id', 'certificate_number'], 'tt_certificate_number_uq');
                    $table->unique(['company_id', 'user_id', 'program_id', 'assignment_id'], 'tt_certificate_assignment_uq');
                });

        Schema::create('tt_channel_links', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('program_id')->nullable()->constrained('tt_programs')->cascadeOnDelete();
                    $table->foreignId('assignment_id')->nullable()->constrained('tt_assignments')->cascadeOnDelete();
                    $table->string('channel_type', 40);
                    $table->string('external_channel_id', 160)->nullable();
                    $table->string('slug', 190);
                    $table->string('status', 24)->default('active');
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->unique(['company_id', 'slug'], 'tt_channel_company_slug_uq');
                });

    }

    public function down(): void
    {
        foreach (['tt_channel_links', 'tt_certificates', 'tt_competency_grants', 'tt_competencies'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
