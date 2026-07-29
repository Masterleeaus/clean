<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Infrastructure\Models\Assessment;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Attempt;
use App\Domains\TitanTrain\Infrastructure\Models\AttemptAnswer;
use App\Domains\TitanTrain\Infrastructure\Models\Question;
use App\Models\User;
use App\Domains\TitanTrain\Domain\Exceptions\TitanTrainRuleViolation;
use Illuminate\Support\Facades\DB;

final class AssessmentService
{
    public function __construct(private TitanTrainEventPublisher $events) {}

    public function start(Assignment $assignment, Assessment $assessment, User $actor): Attempt
    {
        if ((int) $assignment->user_id !== (int) $actor->getKey()) {
            throw new TitanTrainRuleViolation('A learner may start only their own assessment.');
        }
        if ((int) $assessment->program_id !== (int) $assignment->program_id || $assessment->kind !== 'knowledge') {
            throw new TitanTrainRuleViolation('This assessment is not available for learner self-service.');
        }
        if ($assignment->lessons_completed_at === null) {
            throw new TitanTrainRuleViolation('Required lessons must be completed first.');
        }

        return DB::transaction(function () use ($assignment, $assessment, $actor): Attempt {
            Assignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            $existing = Attempt::query()->where('assignment_id', $assignment->id)->where('assessment_id', $assessment->id)->where('status', 'in_progress')->first();
            if ($existing !== null) {
                return $existing->load('assessment.questions');
            }
            $latest = Attempt::query()->where('assignment_id', $assignment->id)->where('assessment_id', $assessment->id)->lockForUpdate()->max('attempt_number');
            $next = ((int) $latest) + 1;
            if ($next > (int) $assessment->attempt_limit) {
                throw new TitanTrainRuleViolation('Assessment attempt limit reached.');
            }
            $attempt = Attempt::query()->create([
                'company_id' => $assignment->company_id,
                'assessment_id' => $assessment->id,
                'assignment_id' => $assignment->id,
                'user_id' => $actor->getKey(),
                'attempt_number' => $next,
                'status' => 'in_progress',
                'result' => 'pending',
                'started_at' => now(),
            ]);
            $this->events->publish('titan_train.assessment.started', 'titan_train.attempt', $attempt->public_id, (int) $actor->getKey(), [
                'assignment_id' => $assignment->public_id, 'assessment_id' => $assessment->public_id, 'attempt_number' => $next,
            ]);
            return $attempt->load('assessment.questions');
        });
    }

    /** @param array<string,mixed> $answers */
    public function submit(Attempt $attempt, User $actor, array $answers): Attempt
    {
        if ((int) $attempt->user_id !== (int) $actor->getKey() || $attempt->status !== 'in_progress') {
            throw new TitanTrainRuleViolation('This assessment attempt cannot be submitted.');
        }

        return DB::transaction(function () use ($attempt, $actor, $answers): Attempt {
            $attempt = Attempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($attempt->status !== 'in_progress') {
                throw new TitanTrainRuleViolation('This assessment attempt has already been submitted.');
            }
            $questions = $attempt->assessment()->with('questions')->firstOrFail()->questions;
            $maximum = 0.0;
            $awarded = 0.0;
            foreach ($questions as $question) {
                $maximum += (float) $question->points;
                $value = $answers[$question->public_id] ?? null;
                $normalised = is_array($value) ? array_values(array_map('strval', $value)) : ($value === null ? [] : [(string) $value]);
                sort($normalised);
                $rawCorrect = $question->getRawOriginal('correct_answer');
                $decodedCorrect = is_string($rawCorrect) && $rawCorrect !== '' ? json_decode($rawCorrect, true, 512, JSON_THROW_ON_ERROR) : [];
                $correct = array_values(array_map('strval', (array) $decodedCorrect));
                sort($correct);
                $isCorrect = ! $question->requires_review && $normalised === $correct;
                $points = $isCorrect ? (float) $question->points : 0.0;
                $awarded += $points;
                AttemptAnswer::query()->updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    ['company_id' => $attempt->company_id, 'answer' => $normalised, 'is_correct' => $isCorrect, 'points_awarded' => $points],
                );
            }
            $score = $maximum > 0 ? round(($awarded / $maximum) * 100, 2) : 0.0;
            $passed = $score >= (float) $attempt->assessment->pass_mark;
            $attempt->forceFill([
                'status' => 'graded', 'score' => $score, 'result' => $passed ? 'pass' : 'fail',
                'submitted_at' => now(), 'graded_at' => now(),
            ])->save();
            if ($passed) {
                $attempt->assignment->forceFill(['knowledge_passed_at' => now(), 'status' => 'practical_pending'])->save();
            }
            $this->events->publish('titan_train.assessment.graded', 'titan_train.attempt', $attempt->public_id, (int) $actor->getKey(), [
                'score' => $score, 'result' => $attempt->result,
            ]);
            return $attempt->fresh(['assessment.questions', 'answers']);
        });
    }

    public function recordPracticalPass(Assignment $assignment, User $trainer, ?string $feedback = null): Attempt
    {
        if ($assignment->knowledge_passed_at === null) {
            throw new TitanTrainRuleViolation('The knowledge assessment must be passed first.');
        }
        $assessment = Assessment::query()->where('program_id', $assignment->program_id)->where('kind', 'practical')->firstOrFail();
        return DB::transaction(function () use ($assignment, $assessment, $trainer, $feedback): Attempt {
            Assignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            $existingPass = Attempt::query()
                ->where('assignment_id', $assignment->id)
                ->where('assessment_id', $assessment->id)
                ->where('result', 'pass')
                ->first();
            if ($existingPass !== null) {
                return $existingPass->load(['assignment', 'assessment.questions', 'answers.question']);
            }
            $latest = Attempt::query()->where('assignment_id', $assignment->id)->where('assessment_id', $assessment->id)->lockForUpdate()->max('attempt_number');
            $next = ((int) $latest) + 1;
            if ($next > (int) $assessment->attempt_limit) {
                throw new TitanTrainRuleViolation('Practical assessment attempt limit reached.');
            }
            $attempt = Attempt::query()->create([
                'company_id' => $assignment->company_id, 'assessment_id' => $assessment->id,
                'assignment_id' => $assignment->id, 'user_id' => $assignment->user_id,
                'attempt_number' => $next, 'status' => 'graded', 'score' => 100,
                'result' => 'pass', 'started_at' => now(), 'submitted_at' => now(),
                'graded_at' => now(), 'graded_by_user_id' => $trainer->getKey(), 'feedback' => $feedback,
            ]);
            foreach ($assessment->questions as $question) {
                AttemptAnswer::query()->create([
                    'company_id' => $assignment->company_id, 'attempt_id' => $attempt->id,
                    'question_id' => $question->id, 'answer' => ['observed' => true],
                    'is_correct' => true, 'points_awarded' => $question->points, 'feedback' => $feedback,
                ]);
            }
            $assignment->forceFill(['practical_passed_at' => now(), 'status' => 'ready_to_qualify'])->save();
            $this->events->publish('titan_train.practical.passed', 'titan_train.assignment', $assignment->public_id, (int) $trainer->getKey(), [
                'attempt_id' => $attempt->public_id, 'worker_user_id' => $assignment->user_id,
            ]);
            return $attempt->load('answers');
        });
    }
}
