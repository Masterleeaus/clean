<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\AgentApprovalRequest;
use App\Domains\TitanMoney\Models\AgentRun;
use Illuminate\Database\Eloquent\Model;

final class AgentApprovalService
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function request(AgentRun $run, string $actionKey, Model $resource, array $payload, string $reason): AgentApprovalRequest
    {
        return AgentApprovalRequest::query()->firstOrCreate([
            'company_id' => $this->currentCompany->require()->id,
            'agent_key' => $run->agent_key,
            'action_key' => $actionKey,
            'resource_type' => $resource::class,
            'resource_id' => (string) $resource->getKey(),
            'status' => 'pending',
        ], [
            'agent_run_id' => $run->id,
            'payload_json' => $payload,
            'reason' => $reason,
            'expires_at' => now()->addDays(14),
        ]);
    }
}
