<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Enums\PaymentStatus;
use App\Domains\TitanMoney\Models\Payment;
use App\Domains\TitanMoney\Services\PaymentAllocationService;
use App\Domains\TitanPay\Enums\CollectionStatus;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\Models\GatewayEvent;
use App\Domains\TitanPay\Models\Transaction;
use App\Domains\TitanPay\ValueObjects\WebhookVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LogicException;

final class PaymentReconciliationService
{
    public function __construct(
        private readonly GatewayRegistry $gateways,
        private readonly PaymentAllocationService $allocations,
        private readonly CollectionSessionCompletionService $completion,
    ) {}

    public function handleWebhook(GatewayConnection $connection, Request $request): GatewayEvent
    {
        $provider = $connection->provider;
        $verification = $this->gateways->get($provider)->verifyWebhook($request, $connection);
        $companyId = $connection->company_id;
        if (! $companyId) {
            throw new LogicException('The gateway connection has no company context.');
        }

        app(CurrentCompany::class)->set(Company::query()->findOrFail($companyId));
        $payloadHash = hash('sha256', $request->getContent());

        if (! $verification->valid) {
            $invalidEventId = 'invalid:'.$payloadHash;
            return GatewayEvent::query()->firstOrCreate(
                ['provider' => $provider, 'event_id' => $invalidEventId],
                [
                    'company_id' => $companyId,
                    'event_type' => $verification->eventType,
                    'merchant_account_reference' => $connection->merchant_account_reference,
                    'signature_valid' => false,
                    'payload_hash' => $payloadHash,
                    'payload_encrypted' => $verification->payload,
                    'received_at' => now(),
                    'processed_at' => now(),
                    'processing_status' => 'rejected',
                    'failure_reason' => $verification->failureReason,
                    'idempotency_key' => $provider.':invalid:'.$payloadHash,
                    'metadata_json' => ['claimed_event_id' => $verification->eventId],
                ],
            );
        }

        $event = GatewayEvent::query()
            ->where('provider', $provider)
            ->where('event_id', $verification->eventId)
            ->first();

        if ($event && ! $event->signature_valid) {
            $event->forceFill([
                'company_id' => $companyId,
                'event_type' => $verification->eventType,
                'merchant_account_reference' => $connection->merchant_account_reference,
                'signature_valid' => true,
                'payload_hash' => $payloadHash,
                'payload_encrypted' => $verification->payload,
                'received_at' => now(),
                'processed_at' => null,
                'processing_status' => 'received',
                'failure_reason' => null,
                'idempotency_key' => $provider.':'.$verification->eventId,
            ])->save();
        }

        $event ??= GatewayEvent::query()->create([
            'company_id' => $companyId,
            'provider' => $provider,
            'event_id' => $verification->eventId,
            'event_type' => $verification->eventType,
            'merchant_account_reference' => $connection->merchant_account_reference,
            'signature_valid' => true,
            'payload_hash' => $payloadHash,
            'payload_encrypted' => $verification->payload,
            'received_at' => now(),
            'processing_status' => 'received',
            'idempotency_key' => $provider.':'.$verification->eventId,
        ]);

        if ($event->processed_at) {
            return $event;
        }

        if ($verification->status !== 'completed') {
            $event->update([
                'processed_at' => now(),
                'processing_status' => 'ignored',
                'failure_reason' => $verification->failureReason ?: 'The verified event does not represent a completed payment.',
            ]);
            return $event;
        }

        $this->completeVerifiedPayment($verification, $provider, $event);

        return $event->fresh();
    }

    public function completeVerifiedPayment(WebhookVerification $verification, string $provider, GatewayEvent $event): void
    {
        DB::transaction(function () use ($verification, $provider, $event): void {
            $lockedEvent = GatewayEvent::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            if ($lockedEvent->processed_at) {
                return;
            }

            $session = CollectionSession::query()
                ->where('company_id', $lockedEvent->company_id)
                ->where(function ($query) use ($verification): void {
                    $query->where('reference', $verification->sessionReference)
                        ->orWhere('gateway_reference', $verification->sessionReference);
                })
                ->lockForUpdate()
                ->first();

            if (! $session) {
                $lockedEvent->update([
                    'processed_at' => now(),
                    'processing_status' => 'unmatched',
                    'failure_reason' => 'Collection session not found.',
                ]);
                return;
            }

            if ($verification->amountMinor !== $session->amount_minor
                || strtoupper((string) $verification->currencyCode) !== strtoupper($session->currency_code)) {
                $lockedEvent->update([
                    'processed_at' => now(),
                    'processing_status' => 'rejected',
                    'failure_reason' => 'Amount or currency mismatch.',
                ]);
                return;
            }

            if ($session->status === CollectionStatus::Completed) {
                $lockedEvent->update(['processed_at' => now(), 'processing_status' => 'duplicate']);
                return;
            }

            $transaction = Transaction::query()->firstOrCreate(
                ['provider' => $provider, 'gateway_transaction_id' => $verification->transactionId],
                [
                    'company_id' => $session->company_id,
                    'collection_session_id' => $session->id,
                    'idempotency_key' => $provider.':'.$verification->transactionId,
                    'type' => 'payment',
                    'status' => 'verified',
                    'amount_minor' => $verification->amountMinor,
                    'fee_minor' => 0,
                    'currency_code' => $verification->currencyCode,
                    'reference' => $session->reference,
                    'verified_at' => now(),
                    'metadata_json' => ['event_id' => $verification->eventId],
                ],
            );

            if ($transaction->company_id !== $session->company_id) {
                throw new LogicException('Gateway transaction is already associated with another company.');
            }

            $payment = Payment::query()->firstOrCreate(
                [
                    'company_id' => $session->company_id,
                    'gateway_provider' => $provider,
                    'gateway_transaction_id' => $verification->transactionId,
                ],
                [
                    'payment_method' => $provider,
                    'currency_code' => $session->currency_code,
                    'amount_minor' => $session->amount_minor,
                    'unallocated_minor' => $session->amount_minor,
                    'received_at' => now(),
                    'gateway_account_id' => $session->gateway_connection_id,
                    'reference' => $session->reference,
                    'status' => PaymentStatus::Verified,
                    'verified_at' => now(),
                    'metadata_json' => ['titanpay_session_id' => $session->id],
                ],
            );

            if (! $transaction->titan_money_payment_id) {
                $transaction->update(['titan_money_payment_id' => $payment->id]);
            }

            $invoice = $session->invoice()->first();
            if ($invoice
                && $payment->unallocated_minor > 0
                && $invoice->balance_due_minor > 0
                && in_array($invoice->status, [InvoiceStatus::Issued, InvoiceStatus::Overdue, InvoiceStatus::PartiallyPaid], true)) {
                $this->allocations->allocate(
                    $payment,
                    $invoice,
                    min((int) $payment->unallocated_minor, (int) $invoice->balance_due_minor),
                );
            }

            $payment->refresh();
            $hasUnallocated = (int) $payment->unallocated_minor > 0;
            $this->completion->complete($session, $invoice);
            $lockedEvent->update([
                'processed_at' => now(),
                'processing_status' => $hasUnallocated ? 'processed_unallocated' : 'processed',
                'failure_reason' => $hasUnallocated
                    ? 'Verified payment retained with an unallocated balance for finance review.'
                    : null,
            ]);
        });
    }
}
