<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\TitanMoneyOutboxEvent;
use Illuminate\Database\Eloquent\Model;

final class TitanMoneyOutbox
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
    ) {}

    public function record(string $eventType, Model $aggregate, array $payload = []): TitanMoneyOutboxEvent
    {
        return TitanMoneyOutboxEvent::query()->create([
            'company_id' => $this->currentCompany->require()->id,
            'event_type' => $eventType,
            'event_version' => 1,
            'aggregate_type' => $aggregate::class,
            'aggregate_id' => (string) $aggregate->getKey(),
            'aggregate_version' => (int) ($aggregate->version ?? 1),
            'actor_type' => $this->actorContext->actorType(),
            'actor_id' => $this->actorContext->actorId(),
            'conversation_id' => $this->actorContext->conversationId(),
            'correlation_id' => $this->actorContext->correlationId(),
            'causation_id' => $this->actorContext->causationId(),
            'payload_json' => $payload,
            'occurred_at' => now(),
            'attempts' => 0,
        ]);
    }
}
