<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Attempt;
use App\Domains\TitanTrain\Infrastructure\Models\AttemptAnswer;
use App\Domains\TitanTrain\Infrastructure\Models\ChannelLink;
use App\Domains\TitanTrain\Infrastructure\Models\Question;

final class TitanTrainApiPresenter
{
    /** @return array<string,mixed> */
    public function readiness(array $readiness): array
    {
        $assignment = $readiness['assignment'] ?? null;
        $program = $readiness['program'] ?? null;
        $certificate = $readiness['certificate'] ?? null;

        return [
            'installed' => (bool) ($readiness['installed'] ?? false),
            'assigned' => (bool) ($readiness['assigned'] ?? false),
            'ready' => (bool) ($readiness['ready'] ?? false),
            'status' => (string) ($readiness['status'] ?? 'not_installed'),
            'progress_percent' => (int) ($readiness['progress_percent'] ?? 0),
            'required_lessons' => (int) ($readiness['required_lessons'] ?? 0),
            'knowledge_passed' => (bool) ($readiness['knowledge_passed'] ?? false),
            'practical_passed' => (bool) ($readiness['practical_passed'] ?? false),
            'active_competencies' => (int) ($readiness['active_competencies'] ?? 0),
            'required_competencies' => (int) ($readiness['required_competencies'] ?? 0),
            'assignment_id' => $assignment?->public_id,
            'program' => $program === null ? null : [
                'id' => $program->public_id,
                'title' => $program->title,
                'slug' => $program->slug,
            ],
            'certificate' => $certificate === null ? null : [
                'id' => $certificate->public_id,
                'number' => $certificate->certificate_number,
                'status' => $certificate->status,
                'issued_at' => $certificate->issued_at?->toISOString(),
                'expires_at' => $certificate->expires_at?->toISOString(),
            ],
        ];
    }

    /** @return array<string,mixed> */
    public function assignment(Assignment $assignment, bool $includeContent = true): array
    {
        $assignment->loadMissing(['program.modules.lessons', 'program.assessments', 'completions', 'attempts']);
        $completedLessonIds = $assignment->completions->pluck('lesson_id')->map(fn ($id): int => (int) $id)->all();

        $payload = [
            'id' => $assignment->public_id,
            'status' => $assignment->status,
            'cycle_number' => (int) $assignment->cycle_number,
            'progress_percent' => (int) $assignment->progress_percent,
            'due_at' => $assignment->due_at?->toISOString(),
            'started_at' => $assignment->started_at?->toISOString(),
            'lessons_completed_at' => $assignment->lessons_completed_at?->toISOString(),
            'knowledge_passed_at' => $assignment->knowledge_passed_at?->toISOString(),
            'practical_passed_at' => $assignment->practical_passed_at?->toISOString(),
            'completed_at' => $assignment->completed_at?->toISOString(),
            'program' => [
                'id' => $assignment->program->public_id,
                'title' => $assignment->program->title,
                'slug' => $assignment->program->slug,
                'summary' => $assignment->program->summary,
                'status' => $assignment->program->status,
                'estimated_duration_minutes' => (int) $assignment->program->estimated_duration_minutes,
            ],
            'attempts' => $assignment->attempts->map(fn (Attempt $attempt): array => $this->attempt($attempt, false))->values()->all(),
        ];

        if ($includeContent) {
            $payload['program']['modules'] = $assignment->program->modules->map(fn ($module): array => [
                'id' => $module->public_id,
                'title' => $module->title,
                'description' => $module->description,
                'position' => (int) $module->position,
                'lessons' => $module->lessons->map(fn ($lesson): array => [
                    'id' => $lesson->public_id,
                    'title' => $lesson->title,
                    'content_type' => $lesson->content_type,
                    'body' => $lesson->body,
                    'duration_minutes' => (int) $lesson->duration_minutes,
                    'position' => (int) $lesson->position,
                    'required' => (bool) $lesson->is_required,
                    'skippable' => (bool) $lesson->is_skippable,
                    'completed' => in_array((int) $lesson->id, $completedLessonIds, true),
                ])->values()->all(),
            ])->values()->all();

            $payload['program']['assessments'] = $assignment->program->assessments->map(fn ($assessment): array => [
                'id' => $assessment->public_id,
                'kind' => $assessment->kind,
                'title' => $assessment->title,
                'instructions' => $assessment->instructions,
                'pass_mark' => (int) $assessment->pass_mark,
                'attempt_limit' => (int) $assessment->attempt_limit,
                'duration_minutes' => (int) $assessment->duration_minutes,
                'status' => $assessment->status,
            ])->values()->all();
        }

        return $payload;
    }

    /** @return array<string,mixed> */
    public function attempt(Attempt $attempt, bool $includeQuestions = true): array
    {
        $attempt->loadMissing(['assignment', 'assessment.questions', 'answers.question']);
        $payload = [
            'id' => $attempt->public_id,
            'assessment_id' => $attempt->assessment->public_id,
            'assignment_id' => $attempt->assignment?->public_id,
            'attempt_number' => (int) $attempt->attempt_number,
            'status' => $attempt->status,
            'score' => $attempt->score === null ? null : (float) $attempt->score,
            'result' => $attempt->result,
            'feedback' => $attempt->feedback,
            'started_at' => $attempt->started_at?->toISOString(),
            'submitted_at' => $attempt->submitted_at?->toISOString(),
            'graded_at' => $attempt->graded_at?->toISOString(),
        ];

        if ($includeQuestions) {
            $payload['questions'] = $attempt->assessment->questions->map(fn (Question $question): array => [
                'id' => $question->public_id,
                'type' => $question->type,
                'prompt' => $question->prompt,
                'options' => $question->options,
                'points' => (float) $question->points,
                'position' => (int) $question->position,
                'requires_review' => (bool) $question->requires_review,
            ])->values()->all();
        }

        if ($attempt->relationLoaded('answers')) {
            $payload['answers'] = $attempt->answers->map(fn (AttemptAnswer $answer): array => [
                'question_id' => $answer->question?->public_id,
                'answer' => $answer->answer,
                'is_correct' => $answer->is_correct,
                'points_awarded' => $answer->points_awarded === null ? null : (float) $answer->points_awarded,
                'feedback' => $answer->feedback,
            ])->values()->all();
        }

        return $payload;
    }

    /** @return array<string,mixed> */
    public function channel(ChannelLink $link): array
    {
        return [
            'id' => $link->public_id,
            'type' => $link->channel_type,
            'slug' => $link->slug,
            'external_channel_id' => $link->external_channel_id,
            'status' => $link->status,
            'metadata' => $link->metadata,
        ];
    }
}
