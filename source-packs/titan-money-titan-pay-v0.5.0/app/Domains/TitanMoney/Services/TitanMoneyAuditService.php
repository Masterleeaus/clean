<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\TitanMoneyAuditLog;
use Illuminate\Database\Eloquent\Model;

final class TitanMoneyAuditService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
    ) {}

    public function record(Model $model, string $action, array $before = [], ?string $reason = null): TitanMoneyAuditLog
    {
        $after = $model->toArray();
        $changes = [];
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $field) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;
            if ($old !== $new) {
                $changes[$field] = ['before' => $old, 'after' => $new];
            }
        }

        $payload = [
            'company_id' => $this->currentCompany->require()->id,
            'entity_type' => $model::class,
            'entity_id' => (string) $model->getKey(),
            'action' => $action,
            'actor_user_id' => $this->actorContext->userId(),
            'actor_agent_id' => $this->actorContext->agentId(),
            'device_id' => $this->actorContext->deviceId(),
            'conversation_id' => $this->actorContext->conversationId(),
            'correlation_id' => $this->actorContext->correlationId(),
            'causation_id' => $this->actorContext->causationId(),
            'reason' => $reason,
            'before_state_json' => $before,
            'after_state_json' => $after,
            'changes_json' => $changes,
            'created_at' => now(),
        ];
        $payload['signature'] = hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), (string) config('app.key'));

        return TitanMoneyAuditLog::query()->create($payload);
    }
}
