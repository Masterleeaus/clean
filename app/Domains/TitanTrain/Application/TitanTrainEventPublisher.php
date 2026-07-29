<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class TitanTrainEventPublisher
{
    public function __construct(
        private TenantContextContract $tenant,
        private OperationContextContract $operation,
    ) {}

    /** @param array<string,mixed> $payload */
    public function publish(string $name, string $aggregateType, string $aggregatePublicId, ?int $actorId, array $payload = []): ?string
    {
        if (! Schema::hasTable('tz_domain_events')) {
            return null;
        }

        $eventId = (string) Str::ulid();
        $occurredAt = now();
        $correlationId = $this->operation->hasContext() ? $this->operation->correlationId() : (string) Str::uuid();
        $causationId = $this->operation->hasContext() ? $this->operation->causationId() : null;
        $companyId = $this->tenant->companyId();

        DB::table('tz_domain_events')->insert([
            'event_id' => $eventId,
            'event_name' => $name,
            'event_version' => 1,
            'company_id' => $companyId,
            'actor_user_id' => $actorId,
            'aggregate_type' => $aggregateType,
            'aggregate_public_id' => $aggregatePublicId,
            'correlation_id' => $correlationId,
            'causation_id' => $causationId,
            'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
            'occurred_at' => $occurredAt,
            'created_at' => $occurredAt,
            'updated_at' => $occurredAt,
        ]);

        if (Schema::hasTable('tz_outbox_messages') && (bool) config('workcore.outbox.enabled', true)) {
            DB::table('tz_outbox_messages')->insert([
                'message_id' => (string) Str::ulid(),
                'company_id' => $companyId,
                'topic' => 'titan_train.domain_event',
                'event_id' => $eventId,
                'correlation_id' => $correlationId,
                'causation_id' => $causationId,
                'payload_json' => json_encode([
                    'event_id' => $eventId,
                    'event_name' => $name,
                    'company_id' => $companyId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_public_id' => $aggregatePublicId,
                    'payload' => $payload,
                    'occurred_at' => $occurredAt->toISOString(),
                ], JSON_THROW_ON_ERROR),
                'status' => 'pending',
                'attempts' => 0,
                'available_at' => $occurredAt,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ]);
        }

        return $eventId;
    }
}
