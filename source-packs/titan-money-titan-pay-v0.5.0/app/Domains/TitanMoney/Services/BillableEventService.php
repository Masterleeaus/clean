<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\BillableEvent;
use App\Domains\TitanMoney\Models\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

final class BillableEventService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
    ) {}

    public function ingest(array $data): BillableEvent
    {
        $company = $this->currentCompany->require();
        $customer = Customer::query()->find($data['customer_id'] ?? null);
        if (! $customer) {
            throw new AuthorizationException('The billable event customer does not belong to the active company.');
        }

        $sourceType = (string) $data['source_type'];
        $sourceId = (string) $data['source_id'];
        $sourceVersion = isset($data['source_version']) ? (string) $data['source_version'] : null;
        $idempotency = (string) ($data['idempotency_key'] ?? implode(':', [$sourceType, $sourceId, $sourceVersion ?: '1']));

        return BillableEvent::query()->firstOrCreate([
            'company_id' => $company->id,
            'idempotency_key' => $idempotency,
        ], [
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_version' => $sourceVersion,
            'customer_id' => $customer->id,
            'payload_json' => $data['payload'],
            'status' => 'pending',
            'occurred_at' => $data['occurred_at'] ?? now(),
            'available_at' => $data['available_at'] ?? now(),
            'correlation_id' => $this->actorContext->correlationId() ?: (string) Str::ulid(),
        ]);
    }
}
