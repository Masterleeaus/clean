<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Domains\WorkCore\System\Host\Contracts\ToolBridgeContract;
use App\Http\Controllers\Controller;
use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CapabilityController extends Controller
{
    public function __invoke(
        ActiveCompanyContext $context,
        CapabilityRegistry $capabilities,
        ToolBridgeContract $tools,
    ): JsonResponse {
        $extensions = Schema::hasTable('titan_extensions')
            ? DB::table('titan_extensions')
                ->select(['id', 'name', 'version', 'type', 'enabled', 'compatible', 'status', 'last_error'])
                ->orderBy('id')
                ->get()
                ->map(static fn ($row): array => (array) $row)
                ->all()
            : [];

        return response()->json([
            'data' => [
                'company_id' => $context->companyId,
                'capabilities' => $capabilities->all(),
                'workcore_tools' => $tools->tools(),
                'extensions' => $extensions,
            ],
        ]);
    }
}
