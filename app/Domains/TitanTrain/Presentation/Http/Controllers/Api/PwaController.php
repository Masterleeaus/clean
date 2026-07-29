<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Presentation\Http\Controllers\Api;

use App\Domains\TitanTrain\Application\PwaBootstrapService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PwaController extends Controller
{
    public function bootstrap(Request $request, PwaBootstrapService $bootstrap): JsonResponse
    {
        return response()->json(['data' => $bootstrap->build($request->user())]);
    }
}
