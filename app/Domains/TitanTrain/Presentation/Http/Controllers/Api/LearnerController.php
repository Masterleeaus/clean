<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Presentation\Http\Controllers\Api;

use App\Domains\TitanTrain\Application\TitanTrainAccess;
use App\Domains\TitanTrain\Application\TitanTrainApiPresenter;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LearnerController extends Controller
{
    public function index(Request $request, TitanTrainAccess $access, TitanTrainApiPresenter $presenter): JsonResponse
    {
        $access->membership($request->user());
        $assignments = Assignment::query()
            ->where('user_id', $request->user()->getKey())
            ->with(['program.modules.lessons', 'completions', 'program.assessments', 'attempts.assessment'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $assignments->map(fn (Assignment $assignment): array => $presenter->assignment($assignment))->values()->all(),
        ]);
    }

    public function show(Request $request, Assignment $assignment, TitanTrainAccess $access, TitanTrainApiPresenter $presenter): JsonResponse
    {
        $access->membership($request->user());
        abort_unless((int) $assignment->user_id === (int) $request->user()->getKey() || $access->isManager($request->user()), 403);

        return response()->json(['data' => $presenter->assignment($assignment)]);
    }
}
