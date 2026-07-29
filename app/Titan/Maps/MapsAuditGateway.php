<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Extensions\TitanMapsIntelligence\Contracts\AuditRecorder as MapsAuditRecorder;
use App\Titan\Audit\AuditRecorder as TitanAuditRecorder;
use Illuminate\Support\Str;

final class MapsAuditGateway implements MapsAuditRecorder
{
    public function __construct(private readonly TitanAuditRecorder $audit) {}

    public function record(array $record): void
    {
        $companyId = isset($record['company_id']) && is_numeric($record['company_id'])
            ? (int) $record['company_id']
            : null;
        $userId = isset($record['user_id']) && is_numeric($record['user_id'])
            ? (int) $record['user_id']
            : (isset($record['requested_by_user_id']) && is_numeric($record['requested_by_user_id'])
                ? (int) $record['requested_by_user_id']
                : null);
        $action = trim((string) ($record['type'] ?? $record['action'] ?? 'maps.unknown'));
        $correlation = $this->uuidOrNull($record['correlation_id'] ?? null);
        $causation = $this->uuidOrNull($record['causation_id'] ?? null);

        $this->audit->record([
            'company_id' => $companyId,
            'user_id' => $userId,
            'agent_id' => $record['agent_id'] ?? null,
            'conversation_id' => $record['conversation_id'] ?? null,
            'action' => $action !== '' ? $action : 'maps.unknown',
            'entity_type' => $this->entityType($record),
            'entity_id' => $this->entityId($record),
            'reason' => $record['reason'] ?? null,
            'correlation_id' => $correlation,
            'causation_id' => $causation,
            'metadata' => $record,
        ]);
    }

    private function uuidOrNull(mixed $value): ?string
    {
        $candidate = is_string($value) ? trim($value) : '';

        return $candidate !== '' && Str::isUuid($candidate) ? $candidate : null;
    }

    private function entityType(array $record): ?string
    {
        foreach (['candidate_id' => 'maps_candidate', 'search_id' => 'maps_search', 'analysis_id' => 'maps_territory_analysis'] as $key => $type) {
            if (isset($record[$key])) {
                return $type;
            }
        }

        return null;
    }

    private function entityId(array $record): ?string
    {
        foreach (['candidate_id', 'search_id', 'analysis_id'] as $key) {
            if (isset($record[$key])) {
                return (string) $record[$key];
            }
        }

        return null;
    }
}
