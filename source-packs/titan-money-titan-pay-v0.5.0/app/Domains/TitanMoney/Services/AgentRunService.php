<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\AgentRun;
use App\Domains\TitanMoney\Models\AutomationPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class AgentRunService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
    ) {}

    public function start(AutomationPolicy $policy, string $agentId, string $triggerType, array $trigger = [], bool $dryRun = false, ?string $correlationId = null): AgentRun
    {
        $correlationId = $correlationId !== null && Str::isUlid($correlationId)
            ? $correlationId
            : (string) Str::ulid();
        $conversationId = $this->actorContext->conversationId();
        $this->actorContext->setAgent($agentId, $correlationId, null, $conversationId);

        return AgentRun::query()->create([
            'company_id' => $this->currentCompany->require()->id,
            'automation_policy_id' => $policy->id,
            'agent_key' => $agentId,
            'trigger_type' => $triggerType,
            'status' => 'running',
            'dry_run' => $dryRun,
            'trigger_json' => $trigger,
            'correlation_id' => $correlationId,
            'started_at' => now(),
        ]);
    }

    public function complete(AgentRun $run, int $examined, int $acted, int $escalated, array $result = [], array $decisions = []): AgentRun
    {
        $run->update([
            'status' => 'completed',
            'items_examined' => $examined,
            'items_acted' => $acted,
            'items_escalated' => $escalated,
            'result_json' => $result,
            'decision_json' => $decisions,
            'completed_at' => now(),
        ]);
        $run->policy?->update(['last_run_at'=>now()]);

        return $run->fresh();
    }

    public function fail(AgentRun $run, Throwable $error, int $examined = 0, int $acted = 0, int $escalated = 0): AgentRun
    {
        Log::error('Titan Money agent run failed.', [
            'agent_run_id' => $run->id,
            'company_id' => $run->company_id,
            'agent_key' => $run->agent_key,
            'correlation_id' => $run->correlation_id,
            'exception' => $error,
        ]);

        $run->update([
            'status'=>'failed',
            'items_examined'=>$examined,
            'items_acted'=>$acted,
            'items_escalated'=>$escalated,
            'error_message'=>'Agent execution failed. Review server logs using correlation ID '.($run->correlation_id ?: 'unavailable').'.',
            'completed_at'=>now(),
        ]);

        return $run->fresh();
    }
}
