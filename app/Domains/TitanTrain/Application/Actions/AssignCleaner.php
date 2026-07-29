<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application\Actions;

use App\Domains\TitanTrain\Application\CleanerOnboardingService;
use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\WorkCore\System\Actions\ActionHandlerResult;
use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\References\TypedReference;
use App\Models\User;

final class AssignCleaner implements BusinessActionHandlerContract
{
    public function __construct(private CleanerOnboardingService $onboarding, private TitanTrainAccess $access) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $cleaner = User::query()->findOrFail((int) ($request->payload['user_id'] ?? 0));
        $actor = User::query()->findOrFail($request->actorId);
        $this->access->requireManager($actor);
        $assignment = $this->onboarding->assign(
            $cleaner,
            $actor,
            isset($request->payload['branch_id']) ? (int) $request->payload['branch_id'] : null,
            now()->addDays((int) ($request->payload['due_days'] ?? config('titan_train.default_due_days', 14))),
        );

        return new ActionHandlerResult(
            ['assignment' => ['id' => $assignment->public_id, 'status' => $assignment->status, 'progress_percent' => (int) $assignment->progress_percent]],
            new TypedReference('titan_train_assignment', $assignment->public_id),
        );
    }
}
