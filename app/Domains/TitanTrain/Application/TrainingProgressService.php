<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Lesson;
use App\Domains\TitanTrain\Infrastructure\Models\LessonCompletion;
use App\Models\User;
use App\Domains\TitanTrain\Domain\Exceptions\TitanTrainRuleViolation;
use Illuminate\Support\Facades\DB;

final class TrainingProgressService
{
    public function __construct(private TitanTrainEventPublisher $events) {}

    public function complete(Assignment $assignment, Lesson $lesson, User $actor): Assignment
    {
        if ((int) $assignment->user_id !== (int) $actor->getKey()) {
            throw new TitanTrainRuleViolation('A learner may complete only their own assignment.');
        }
        $lessonProgramId = (int) $lesson->module()->value('program_id');
        if ($lessonProgramId !== (int) $assignment->program_id) {
            throw new TitanTrainRuleViolation('The lesson does not belong to this assignment.');
        }

        return DB::transaction(function () use ($assignment, $lesson, $actor): Assignment {
            LessonCompletion::query()->firstOrCreate(
                ['assignment_id' => $assignment->id, 'lesson_id' => $lesson->id],
                ['company_id' => $assignment->company_id, 'user_id' => $actor->getKey(), 'completed_at' => now()],
            );

            $required = Lesson::query()->whereHas('module', fn ($query) => $query->where('program_id', $assignment->program_id))->where('is_required', true)->count();
            $completed = LessonCompletion::query()->where('assignment_id', $assignment->id)->count();
            $percent = $required > 0 ? min(100, (int) floor(($completed / $required) * 100)) : 100;
            $updates = [
                'progress_percent' => $percent,
                'started_at' => $assignment->started_at ?? now(),
                'status' => $percent >= 100 ? 'knowledge_pending' : 'in_progress',
            ];
            if ($percent >= 100) {
                $updates['lessons_completed_at'] = $assignment->lessons_completed_at ?? now();
            }
            $assignment->forceFill($updates)->save();

            $this->events->publish('titan_train.lesson.completed', 'titan_train.assignment', $assignment->public_id, (int) $actor->getKey(), [
                'lesson_id' => $lesson->public_id, 'progress_percent' => $percent,
            ]);

            return $assignment->fresh(['program.modules.lessons', 'completions']);
        });
    }
}
