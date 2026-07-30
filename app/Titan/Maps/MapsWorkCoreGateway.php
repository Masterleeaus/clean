<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Domains\WorkCore\System\Actions\ActionRequest;
use App\Domains\WorkCore\System\Actions\BusinessActionDispatcher;
use App\Domains\WorkCore\System\External\ExternalCandidateLookup;
use App\Extensions\TitanMapsIntelligence\Contracts\WorkCoreCandidateGateway;
use App\Extensions\TitanMapsIntelligence\Contracts\WorkCoreCandidateLookup;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class MapsWorkCoreGateway implements WorkCoreCandidateGateway, WorkCoreCandidateLookup
{
    public function __construct(
        private readonly ActiveCompanyContext $context,
        private readonly BusinessActionDispatcher $dispatcher,
        private readonly ExternalCandidateLookup $lookup,
    ) {}

    public function promote(string $companyId, string $targetType, array $fields, array $context): array
    {
        $this->assertCompany($companyId);
        $candidateId = trim((string) ($context['candidate_id'] ?? ''));
        if ($candidateId === '') {
            throw new InvalidArgumentException('Candidate promotion requires a candidate id.');
        }

        [$action, $payload] = $this->promotionPayload($targetType, $fields, $candidateId);
        $result = $this->dispatcher->dispatch(new ActionRequest(
            key: $action,
            payload: $payload,
            companyId: $this->context->companyId,
            actorId: $this->context->userId,
            idempotencyKey: 'maps:' . hash('sha256', $candidateId . '|' . $targetType),
            confirmationId: 'maps-approved:' . $candidateId,
            source: 'titan_maps_intelligence',
        ));

        return [
            'entity_id' => isset($result->data['public_id'])
                ? (string) $result->data['public_id']
                : (isset($result->data['id']) ? (string) $result->data['id'] : null),
            'entity_type' => $targetType,
            'command_id' => $result->auditId,
            'event_id' => $result->eventIds[0] ?? null,
            'correlation_id' => $result->correlationId,
            'replayed' => $result->replayed,
            'data' => $result->data,
        ];
    }

    public function find(string $companyId, string $entityType, string $entityId): ?array
    {
        $this->assertCompany($companyId);

        return $this->lookup->find($this->context->companyId, $entityType, $entityId);
    }

    private function assertCompany(string $companyId): void
    {
        if (! is_numeric($companyId) || (int) $companyId !== $this->context->companyId) {
            throw new \LogicException('Maps WorkCore access must use the active Titan company.');
        }
    }

    /** @return array{0:string,1:array<string,mixed>} */
    private function promotionPayload(string $targetType, array $fields, string $candidateId): array
    {
        $name = trim((string) ($fields['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('WorkCore promotion requires the approved business name.');
        }

        $phone = $this->nullableString($fields['phone'] ?? null);
        $email = $this->nullableString($fields['public_email'] ?? null);
        $website = $this->nullableString($fields['website'] ?? null);
        $address = $this->nullableString($fields['address'] ?? null);
        $categories = array_values(array_filter((array) ($fields['categories'] ?? []), 'is_string'));
        $note = 'Promoted from Titan Maps Intelligence candidate ' . $candidateId;
        if ($website !== null) {
            $note .= '; website: ' . $website;
        }
        if ($categories !== []) {
            $note .= '; categories: ' . implode(', ', $categories);
        }

        return match ($targetType) {
            'lead' => ['workcore.lead.create', [
                'name' => $name,
                'company_name' => $name,
                'phone' => $phone,
                'email' => $email,
                'note' => $note,
            ]],
            'customer' => ['workcore.customer.create', [
                'name' => $name,
                'company_name' => $name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'note' => $note,
            ]],
            'supplier', 'provider', 'contractor' => ['workcore.supply.supplier.create', [
                'supplier_number' => 'MAP-' . Str::upper(substr(hash('sha256', $candidateId), 0, 12)),
                'name' => $name,
                'status' => 'active',
                'phone' => $phone,
                'email' => $email,
                'website' => $website,
                'address' => $address,
                'currency' => 'AUD',
                'capabilities' => $categories,
                'metadata' => [
                    'source' => 'titan_maps_intelligence',
                    'candidate_id' => $candidateId,
                    'classification' => $targetType,
                ],
            ]],
            default => throw new InvalidArgumentException('The selected WorkCore promotion target is not supported.'),
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
