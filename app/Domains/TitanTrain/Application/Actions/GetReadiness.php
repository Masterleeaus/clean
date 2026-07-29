<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application\Actions;

use App\Domains\TitanTrain\Application\CleanerReadinessService;
use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Application\TitanTrainApiPresenter;
use App\Domains\WorkCore\System\Actions\ActionHandlerResult;
use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Models\User;

final class GetReadiness implements BusinessActionHandlerContract
{
    public function __construct(
        private CleanerReadinessService $readiness,
        private TitanTrainAccess $access,
        private TitanTrainApiPresenter $presenter,
    ) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $actor = User::query()->findOrFail($request->actorId);
        $this->access->membership($actor);
        $userId = (int) ($request->payload['user_id'] ?? $request->actorId);
        $target = User::query()->findOrFail($userId);
        if ($userId !== $request->actorId) {
            $this->access->requireManager($actor);
            $this->access->membership($target);
        }

        return new ActionHandlerResult(['readiness' => $this->presenter->readiness($this->readiness->forUser($target))]);
    }
}
