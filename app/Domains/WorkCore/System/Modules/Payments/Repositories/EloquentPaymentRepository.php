<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Repositories;

use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use App\Domains\WorkCore\System\Modules\Payments\Domain\PaymentMatchScorer;
use App\Domains\WorkCore\System\Persistence\TenantScopedRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class EloquentPaymentRepository extends TenantScopedRepository implements PaymentRepositoryContract
{
    public function __construct(ConnectionInterface $db, TenantContextContract $tenant, private readonly FinanceRepositoryContract $finance)
    {
        parent::__construct($db, $tenant);
    }

    public function createSession(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $invoice = isset($data['invoice_public_id']) ? $this->record('tz_finance_invoices', $companyId, (string) $data['invoice_public_id']) : null;
        $amount = (int) ($data['amount_minor'] ?? ($invoice->balance_minor ?? 0));
        $currency = strtoupper((string) ($data['currency'] ?? ($invoice->currency ?? 'AUD')));
        if ($amount <= 0 || ($invoice && $amount > (int) $invoice->balance_minor)) throw new InvalidArgumentException('Payment session amount is invalid.');
        if ($invoice && $currency !== $invoice->currency) throw new InvalidArgumentException('Payment session currency mismatch.');
        $token = bin2hex(random_bytes(24));
        $publicId = (string) Str::ulid();
        $this->db->table('tz_payment_sessions')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'invoice_id' => $invoice?->id,
            'payable_type' => $data['payable_type'] ?? ($invoice ? 'invoice' : 'external'),
            'payable_public_id' => $data['payable_public_id'] ?? ($invoice->public_id ?? null),
            'method' => strtolower((string) ($data['method'] ?? 'bank_transfer')),
            'provider' => $data['provider'] ?? null, 'status' => 'pending', 'amount_minor' => $amount,
            'currency' => $currency, 'reference' => $data['reference'] ?? ($invoice->invoice_number ?? null),
            'session_token_hash' => hash('sha256', $token), 'expires_at' => $data['expires_at'] ?? now()->addHours(24),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_payment_sessions', $companyId, $publicId);
    }

    public function recordAttempt(string $sessionPublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $session = $this->record('tz_payment_sessions', $companyId, $sessionPublicId);
        $publicId = (string) Str::ulid();
        $safeResponse = $data['safe_response'] ?? null;
        $this->db->table('tz_payment_attempts')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'session_id' => $session->id,
            'provider' => $data['provider'] ?? $session->provider, 'status' => $data['status'] ?? 'started',
            'external_reference' => $data['external_reference'] ?? null, 'amount_minor' => (int) ($data['amount_minor'] ?? $session->amount_minor),
            'fee_minor' => max(0, (int) ($data['fee_minor'] ?? 0)), 'currency' => $data['currency'] ?? $session->currency,
            'failure_code' => $data['failure_code'] ?? null, 'safe_failure_message' => $data['safe_failure_message'] ?? null,
            'response_hash' => $safeResponse === null ? null : hash('sha256', $this->canonicalJson($safeResponse)),
            'metadata' => $this->json($data['metadata'] ?? null), 'completed_at' => $data['completed_at'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_payment_attempts', $companyId, $publicId);
    }

    public function recordObservation(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $provider = strtolower(trim((string) ($data['provider'] ?? '')));
        $externalEventId = trim((string) ($data['external_event_id'] ?? ''));
        if ($provider === '' || $externalEventId === '') throw new InvalidArgumentException('Provider and external event ID are required.');
        $existing = $this->db->table('tz_payment_observations')->where('company_id', $companyId)
            ->where('provider', $provider)->where('external_event_id', $externalEventId)->first();
        if ($existing) return (array) $existing;
        $amount = (int) ($data['amount_minor'] ?? 0);
        if ($amount <= 0) throw new InvalidArgumentException('Payment observation amount must be positive.');
        $safePayload = $data['safe_payload'] ?? [];
        $publicId = (string) Str::ulid();
        $this->db->table('tz_payment_observations')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'provider' => $provider,
            'external_event_id' => $externalEventId, 'observation_type' => $data['observation_type'] ?? 'payment_received',
            'amount_minor' => $amount, 'currency' => strtoupper((string) ($data['currency'] ?? 'AUD')),
            'reference' => $data['reference'] ?? null, 'payer_name' => $data['payer_name'] ?? null,
            'observed_at' => $data['observed_at'] ?? now(), 'payload_hash' => hash('sha256', $this->canonicalJson($safePayload)),
            'safe_payload' => $this->json($safePayload), 'status' => 'unmatched', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_payment_observations', $companyId, $publicId);
    }

    public function proposeMatches(string $observationPublicId, int $companyId): array
    {
        $this->assertCompany($companyId);
        $observation = $this->record('tz_payment_observations', $companyId, $observationPublicId);
        $invoices = $this->db->table('tz_finance_invoices')->where('company_id', $companyId)->whereNull('deleted_at')
            ->where('currency', $observation->currency)->whereIn('status', ['issued', 'viewed', 'overdue', 'partially_paid'])->limit(100)->get();
        $matches = [];
        foreach ($invoices as $invoice) {
            $scored = PaymentMatchScorer::score([
                'amount_minor' => (int) $observation->amount_minor, 'currency' => $observation->currency,
                'reference' => $observation->reference, 'observed_on' => $observation->observed_at,
            ], [
                'amount_minor' => (int) $invoice->balance_minor, 'currency' => $invoice->currency,
                'reference' => $invoice->invoice_number, 'due_on' => $invoice->due_date,
            ]);
            if ($scored['score'] < 50) continue;
            $existing = $this->db->table('tz_payment_matches')->where('company_id', $companyId)
                ->where('observation_id', $observation->id)->where('invoice_id', $invoice->id)->first();
            $publicId = $existing?->public_id ?? (string) Str::ulid();
            $values = ['score' => $scored['score'], 'reasons' => $this->json($scored['reasons']), 'updated_at' => now()];
            if ($existing) $this->db->table('tz_payment_matches')->where('id', $existing->id)->update($values);
            else $this->db->table('tz_payment_matches')->insert($values + [
                'public_id' => $publicId, 'company_id' => $companyId, 'observation_id' => $observation->id,
                'invoice_id' => $invoice->id, 'status' => 'proposed', 'created_at' => now(),
            ]);
            $matches[] = $this->snapshot('tz_payment_matches', $companyId, (string) $publicId);
        }
        usort($matches, static fn (array $a, array $b) => ((int) $b['score']) <=> ((int) $a['score']));
        return $matches;
    }

    public function reconcilePayment(string $observationPublicId, string $invoicePublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($observationPublicId, $invoicePublicId, $data, $companyId, $actorId): array {
            $observation = $this->locked('tz_payment_observations', $companyId, $observationPublicId);
            if ($observation->status === 'reconciled') {
                $existing = $this->db->table('tz_payment_reconciliations')->where('company_id', $companyId)
                    ->where('observation_id', $observation->id)->first();
                if ($existing) return (array) $existing;
            }
            $invoice = $this->record('tz_finance_invoices', $companyId, $invoicePublicId);
            $amount = (int) ($data['amount_minor'] ?? $observation->amount_minor);
            if ($amount <= 0 || $amount > (int) $invoice->balance_minor || $observation->currency !== $invoice->currency) {
                throw new InvalidArgumentException('Payment reconciliation amount or currency is invalid.');
            }
            $allocation = $this->finance->allocateReceivable($invoicePublicId, [
                'amount_minor' => $amount, 'currency' => $invoice->currency, 'source_type' => 'payment_observation',
                'source_public_id' => $observationPublicId, 'allocated_at' => $data['reconciled_at'] ?? now(),
                'reason' => $data['reason'] ?? 'ZeroPay reconciliation',
            ], $companyId, $actorId);
            $publicId = (string) Str::ulid();
            $matchId = $this->optionalId('tz_payment_matches', $companyId, $data['match_public_id'] ?? null);
            $this->db->table('tz_payment_reconciliations')->insert([
                'public_id' => $publicId, 'company_id' => $companyId, 'observation_id' => $observation->id,
                'invoice_id' => $invoice->id, 'match_id' => $matchId, 'amount_minor' => $amount,
                'currency' => $invoice->currency, 'receivable_allocation_public_id' => $allocation['allocation']['public_id'] ?? null,
                'status' => 'posted', 'reconciled_by_user_id' => $actorId,
                'reconciled_at' => $data['reconciled_at'] ?? now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->db->table('tz_payment_observations')->where('id', $observation->id)->update(['status' => 'reconciled', 'updated_at' => now()]);
            if ($matchId !== null) $this->db->table('tz_payment_matches')->where('id', $matchId)->update([
                'status' => 'accepted', 'reviewed_by_user_id' => $actorId, 'reviewed_at' => now(), 'updated_at' => now(),
            ]);
            return $this->snapshot('tz_payment_reconciliations', $companyId, $publicId);
        }, 3);
    }

    public function paymentSummary(int $companyId, array $filters = []): array
    {
        $this->assertCompany($companyId);
        return [
            'pending_sessions' => $this->db->table('tz_payment_sessions')->where('company_id', $companyId)->where('status', 'pending')->count(),
            'unmatched_observations' => $this->db->table('tz_payment_observations')->where('company_id', $companyId)->where('status', 'unmatched')->count(),
            'proposed_matches' => $this->db->table('tz_payment_matches')->where('company_id', $companyId)->where('status', 'proposed')->count(),
            'reconciled_minor' => (int) $this->db->table('tz_payment_reconciliations')->where('company_id', $companyId)->where('status', 'posted')->sum('amount_minor'),
            'currency' => (string) ($filters['currency'] ?? 'AUD'),
        ];
    }

    public function paymentSession(string $publicId, int $companyId): array
    {
        $this->assertCompany($companyId);
        $session = $this->record('tz_payment_sessions', $companyId, $publicId);
        return ['session' => (array) $session, 'attempts' => array_map(static fn ($row) => (array) $row,
            $this->db->table('tz_payment_attempts')->where('company_id', $companyId)->where('session_id', $session->id)->orderBy('id')->get()->all())];
    }

    private function guard(int $companyId, int $actorId): void { $this->assertCompany($companyId); if ($actorId !== $this->actorId()) throw new InvalidArgumentException('Actor mismatch.'); }
    private function optionalId(string $table, int $companyId, mixed $publicId): ?int { return is_string($publicId) && $publicId !== '' ? (int) $this->record($table, $companyId, $publicId)->id : null; }
    private function record(string $table, int $companyId, string $publicId): object { $row = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->first(); if (! $row) throw new InvalidArgumentException('Payment record not found.'); return $row; }
    private function locked(string $table, int $companyId, string $publicId): object { $row = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->lockForUpdate()->first(); if (! $row) throw new InvalidArgumentException('Payment record not found.'); return $row; }
    private function snapshot(string $table, int $companyId, string $publicId): array { return (array) $this->record($table, $companyId, $publicId); }
    private function json(mixed $value): ?string { return $value === null ? null : json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES); }
    private function canonicalJson(mixed $value): string { if (is_array($value)) ksort($value); return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES); }
}
