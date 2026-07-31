<?php

declare(strict_types=1);

namespace App\Titan\Audit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DatabaseAuditRecorder implements AuditRecorder
{
    public function record(array $record): string
    {
        $id = (string) ($record['id'] ?? Str::uuid());
        $now = now();

        DB::table('titan_audit_logs')->insert([
            'id' => $id,
            'company_id' => $record['company_id'] ?? null,
            'user_id' => $record['user_id'] ?? null,
            'agent_id' => $record['agent_id'] ?? null,
            'conversation_id' => $record['conversation_id'] ?? null,
            'action' => (string) ($record['action'] ?? 'unknown'),
            'entity_type' => $record['entity_type'] ?? null,
            'entity_id' => isset($record['entity_id']) ? (string) $record['entity_id'] : null,
            'before_state' => $this->encode($record['before_state'] ?? null),
            'after_state' => $this->encode($record['after_state'] ?? null),
            'reason' => $record['reason'] ?? null,
            'correlation_id' => $record['correlation_id'] ?? null,
            'causation_id' => $record['causation_id'] ?? null,
            'metadata' => $this->encode($record['metadata'] ?? null),
            'created_at' => $record['created_at'] ?? $now,
        ]);

        return $id;
    }

    private function encode(mixed $value): ?string
    {
        return $value === null ? null : json_encode($value, JSON_THROW_ON_ERROR);
    }
}
