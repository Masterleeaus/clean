<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tt_programs', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->string('title');
                    $table->string('slug');
                    $table->text('summary')->nullable();
                    $table->longText('description')->nullable();
                    $table->string('level', 32)->default('beginner');
                    $table->unsignedInteger('estimated_duration_minutes')->default(0);
                    $table->json('requirements')->nullable();
                    $table->json('outcomes')->nullable();
                    $table->string('status', 24)->default('draft');
                    $table->string('source_type', 80)->nullable();
                    $table->string('source_id', 160)->nullable();
                    $table->timestamp('published_at')->nullable();
                    $table->timestamps();
                    $table->unique(['company_id', 'slug'], 'tt_program_company_slug_uq');
                    $table->index(['company_id', 'status'], 'tt_program_company_status_ix');
                });

        Schema::create('tt_modules', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('program_id')->constrained('tt_programs')->cascadeOnDelete();
                    $table->string('title');
                    $table->text('description')->nullable();
                    $table->unsignedInteger('position');
                    $table->timestamps();
                    $table->unique(['program_id', 'position'], 'tt_module_program_position_uq');
                });

        Schema::create('tt_lessons', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('module_id')->constrained('tt_modules')->cascadeOnDelete();
                    $table->string('title');
                    $table->string('content_type', 32)->default('text');
                    $table->longText('body')->nullable();
                    $table->unsignedInteger('duration_minutes')->default(0);
                    $table->unsignedInteger('position');
                    $table->boolean('is_required')->default(true);
                    $table->boolean('is_skippable')->default(false);
                    $table->timestamps();
                    $table->unique(['module_id', 'position'], 'tt_lesson_module_position_uq');
                });

        Schema::create('tt_assignments', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('branch_id')->nullable()->constrained('tz_branches')->nullOnDelete();
                    $table->foreignId('program_id')->constrained('tt_programs')->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                    $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('assignment_key', 190);
                    $table->unsignedInteger('cycle_number')->default(1);
                    $table->string('status', 32)->default('assigned');
                    $table->unsignedTinyInteger('progress_percent')->default(0);
                    $table->timestamp('due_at')->nullable();
                    $table->timestamp('started_at')->nullable();
                    $table->timestamp('lessons_completed_at')->nullable();
                    $table->timestamp('knowledge_passed_at')->nullable();
                    $table->timestamp('practical_passed_at')->nullable();
                    $table->timestamp('completed_at')->nullable();
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->unique(['company_id', 'user_id', 'assignment_key', 'cycle_number'], 'tt_assignment_identity_uq');
                    $table->index(['company_id', 'user_id', 'status'], 'tt_assignment_user_status_ix');
                });

        Schema::create('tt_lesson_completions', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('assignment_id')->constrained('tt_assignments')->cascadeOnDelete();
                    $table->foreignId('lesson_id')->constrained('tt_lessons')->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                    $table->timestamp('completed_at');
                    $table->unique(['assignment_id', 'lesson_id'], 'tt_lesson_completion_uq');
                });

        Schema::create('tt_assessments', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('program_id')->constrained('tt_programs')->cascadeOnDelete();
                    $table->string('kind', 32);
                    $table->string('title');
                    $table->text('instructions')->nullable();
                    $table->unsignedTinyInteger('pass_mark')->default(80);
                    $table->unsignedTinyInteger('attempt_limit')->default(3);
                    $table->unsignedInteger('duration_minutes')->default(0);
                    $table->string('status', 24)->default('published');
                    $table->timestamps();
                    $table->unique(['program_id', 'kind'], 'tt_assessment_program_kind_uq');
                });

        Schema::create('tt_questions', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('assessment_id')->constrained('tt_assessments')->cascadeOnDelete();
                    $table->string('type', 40);
                    $table->text('prompt');
                    $table->json('options')->nullable();
                    $table->json('correct_answer')->nullable();
                    $table->decimal('points', 8, 2)->default(1);
                    $table->unsignedInteger('position');
                    $table->boolean('requires_review')->default(false);
                    $table->timestamps();
                    $table->unique(['assessment_id', 'position'], 'tt_question_assessment_position_uq');
                });

        Schema::create('tt_attempts', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('assessment_id')->constrained('tt_assessments')->cascadeOnDelete();
                    $table->foreignId('assignment_id')->constrained('tt_assignments')->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                    $table->unsignedInteger('attempt_number');
                    $table->string('status', 24)->default('in_progress');
                    $table->decimal('score', 6, 2)->nullable();
                    $table->string('result', 24)->default('pending');
                    $table->timestamp('started_at');
                    $table->timestamp('submitted_at')->nullable();
                    $table->timestamp('graded_at')->nullable();
                    $table->foreignId('graded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->text('feedback')->nullable();
                    $table->timestamps();
                    $table->unique(['assessment_id', 'assignment_id', 'attempt_number'], 'tt_attempt_number_uq');
                    $table->index(['company_id', 'user_id', 'status'], 'tt_attempt_user_status_ix');
                });

        Schema::create('tt_attempt_answers', function (Blueprint $table): void {
                    $table->id();
                    $table->ulid('public_id')->unique();
                    $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
                    $table->foreignId('attempt_id')->constrained('tt_attempts')->cascadeOnDelete();
                    $table->foreignId('question_id')->constrained('tt_questions')->cascadeOnDelete();
                    $table->json('answer')->nullable();
                    $table->boolean('is_correct')->nullable();
                    $table->decimal('points_awarded', 8, 2)->nullable();
                    $table->text('feedback')->nullable();
                    $table->timestamps();
                    $table->unique(['attempt_id', 'question_id'], 'tt_attempt_answer_uq');
                });

    }

    public function down(): void
    {
        foreach (['tt_attempt_answers', 'tt_attempts', 'tt_questions', 'tt_assessments', 'tt_lesson_completions', 'tt_assignments', 'tt_lessons', 'tt_modules', 'tt_programs'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
