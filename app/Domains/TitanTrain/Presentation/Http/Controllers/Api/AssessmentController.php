<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Presentation\Http\Controllers\Api;

use App\Domains\TitanTrain\Application\AssessmentService;
use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Application\TitanTrainApiPresenter;
use App\Domains\TitanTrain\Infrastructure\Models\Assessment;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Attempt;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssessmentController extends Controller
{
    public function start(
        Request $request,
        Assignment $assignment,
        Assessment $assessment,
        TitanTrainAccess $access,
        AssessmentService $service,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->membership($request->user());
        $attempt = $service->start($assignment, $assessment, $request->user());

        return response()->json(['data' => $presenter->attempt($attempt)], 201);
    }

    public function submit(
        Request $request,
        Attempt $attempt,
        TitanTrainAccess $access,
        AssessmentService $service,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->membership($request->user());
        $validated = $request->validate(['answers' => ['required', 'array']]);
        $graded = $service->submit($attempt, $request->user(), $validated['answers']);

        return response()->json(['data' => $presenter->attempt($graded)]);
    }
}
