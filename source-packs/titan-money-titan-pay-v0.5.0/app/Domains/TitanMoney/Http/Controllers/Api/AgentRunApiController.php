<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers\Api;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Services\TitanMoneyAutomationCoordinator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AgentRunApiController extends Controller
{
    public function store(Request $request, TitanMoneyAutomationCoordinator $coordinator, CurrentCompany $company): JsonResponse
    {
        $data = $request->validate([
            'agent_key'=>'nullable|in:invoice_generation,receivables_follow_up,payment_reconciliation',
            'dry_run'=>'nullable|boolean',
        ]);
        $runs = $coordinator->runEnabled(
            $data['agent_key'] ?? null,
            $company->require()->id,
            (bool) ($data['dry_run'] ?? false),
            'api',
        );

        return response()->json([
            'data' => array_map(
                static fn (AgentRun $run): array => $run->only(['id','agent_key','status','dry_run','items_examined','items_acted','items_escalated','correlation_id','started_at','completed_at']),
                $runs,
            ),
        ]);
    }
}
