<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application\Actions;

use App\Domains\TitanTrain\Application\TrainingProgressService;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Lesson;
use App\Domains\WorkCore\System\Actions\ActionHandlerResult;
use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\References\TypedReference;
use App\Models\User;

final class CompleteLesson implements BusinessActionHandlerContract
{
    public function __construct(private TrainingProgressService $progress) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $assignment = Assignment::query()->where('public_id', (string) ($request->payload['assignment_id'] ?? ''))->firstOrFail();
        $lesson = Lesson::query()->where('public_id', (string) ($request->payload['lesson_id'] ?? ''))->firstOrFail();
        $updated = $this->progress->complete($assignment, $lesson, User::query()->findOrFail($request->actorId));

        return new ActionHandlerResult(
            ['assignment' => ['id' => $updated->public_id, 'status' => $updated->status, 'progress_percent' => $updated->progress_percent]],
            new TypedReference('titan_train_assignment', $updated->public_id),
        );
    }
}
