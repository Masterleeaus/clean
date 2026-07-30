<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Presentation\Http\Controllers\Api;

use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Application\TitanTrainApiPresenter;
use App\Domains\TitanTrain\Application\TrainingProgressService;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Lesson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProgressController extends Controller
{
    public function complete(
        Request $request,
        Assignment $assignment,
        Lesson $lesson,
        TitanTrainAccess $access,
        TrainingProgressService $progress,
        TitanTrainApiPresenter $presenter,
    ): JsonResponse {
        $access->membership($request->user());
        $updated = $progress->complete($assignment, $lesson, $request->user());

        return response()->json(['data' => $presenter->assignment($updated)]);
    }
}
