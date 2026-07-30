<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Presentation\Http\Controllers\Api;

use App\Domains\TitanTrain\Application\AssessmentService;
use App\Domains\TitanTrain\Application\CleanerFoundationInstaller;
use App\Domains\TitanTrain\Application\CleanerOnboardingService;
use App\Domains\TitanTrain\Application\CleanerQualificationService;
use App\Domains\TitanTrain\Application\CleanerReadinessService;
use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Application\TitanTrainApiPresenter;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ManagementController extends Controller
{
    public function install(Request $request, TitanTrainAccess $access, CleanerFoundationInstaller $installer): JsonResponse
    {
        $access->requireManager($request->user());
        $program = $installer->install($request->user());

        return response()->json([
            'data' => ['id' => $program->public_id, 'title' => $program->title, 'slug' => $program->slug, 'status' => $program->status],
            'message' => 'Cleaner foundation installed.',
        ], 201);
    }

    public function assign(
        Request $request,
        User $user,
        TitanTrainAccess $access,
        CleanerOnboardingService $onboarding,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->requireManager($request->user());
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'due_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);
        $assignment = $onboarding->assign(
            $user,
            $request->user(),
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            now()->addDays((int) ($validated['due_days'] ?? config('titan_train.default_due_days', 14))),
        );

        return response()->json(['data' => $presenter->assignment($assignment), 'message' => 'Cleaner training assigned.'], 201);
    }

    public function practical(
        Request $request,
        Assignment $assignment,
        TitanTrainAccess $access,
        AssessmentService $assessments,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->requireManager($request->user());
        $validated = $request->validate(['feedback' => ['nullable', 'string', 'max:4000']]);
        $attempt = $assessments->recordPracticalPass($assignment, $request->user(), $validated['feedback'] ?? null);

        return response()->json(['data' => $presenter->attempt($attempt)]);
    }

    public function finalise(
        Request $request,
        Assignment $assignment,
        TitanTrainAccess $access,
        CleanerQualificationService $qualification,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->requireManager($request->user());
        $result = $qualification->finalise($assignment, $request->user());

        return response()->json(['data' => $presenter->readiness($result), 'message' => 'Cleaner qualification finalised.']);
    }

    public function readiness(
        Request $request,
        User $user,
        TitanTrainAccess $access,
        CleanerReadinessService $readiness,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->requireManager($request->user());
        $access->membership($user);

        return response()->json(['data' => $presenter->readiness($readiness->forUser($user))]);
    }
}
