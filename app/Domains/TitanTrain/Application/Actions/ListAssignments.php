<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application\Actions;

use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\WorkCore\System\Actions\ActionHandlerResult;
use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Models\User;

final class ListAssignments implements BusinessActionHandlerContract
{
    public function __construct(private TitanTrainAccess $access) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $actor = User::query()->findOrFail($request->actorId);
        $this->access->membership($actor);
        $rows = Assignment::query()->where('user_id', $request->actorId)
            ->with(['program', 'completions'])
            ->latest('id')
            ->get()
            ->map(fn (Assignment $assignment): array => [
                'id' => $assignment->public_id,
                'program' => $assignment->program->title,
                'status' => $assignment->status,
                'progress_percent' => (int) $assignment->progress_percent,
                'due_at' => $assignment->due_at?->toISOString(),
            ])->values()->all();

        return new ActionHandlerResult(['assignments' => $rows]);
    }
}
