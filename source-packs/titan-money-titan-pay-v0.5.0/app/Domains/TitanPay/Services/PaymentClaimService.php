<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\TitanMoney\Enums\PaymentStatus;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Payment;
use App\Domains\TitanMoney\Services\PaymentAllocationService;
use App\Domains\TitanPay\Enums\CollectionStatus;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\PaymentClaim;
use App\Domains\TitanPay\Policies\CollectionEligibility;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use LogicException;

final class PaymentClaimService
{
    public function __construct(
        private readonly PaymentAllocationService $allocations,
        private readonly CollectionEligibility $eligibility,
        private readonly ActorContext $actorContext,
        private readonly CollectionSessionCompletionService $completion,
    ) {}

    public function submit(CollectionSession $session, array $data, ?UploadedFile $evidence = null): PaymentClaim
    {
        return DB::transaction(function () use ($session, $data, $evidence): PaymentClaim {
            $lockedSession = CollectionSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();

            if ($lockedSession->isExpired()) {
                $lockedSession->update(['status' => CollectionStatus::Expired]);
                throw new LogicException('Collection session has expired.');
            }
            if (! $this->eligibility->canSubmitClaim($lockedSession->status)) {
                throw new LogicException('This collection session no longer accepts payment claims.');
            }
            if (! in_array($data['method'], ['cash', 'payid', 'bank_transfer'], true)) {
                throw new LogicException('Only manual payment methods can be claimed.');
            }
            if (! in_array($data['method'], $lockedSession->allowed_methods_json ?? [], true)) {
                throw new LogicException('The selected manual payment method is not enabled for this collection session.');
            }

            $remainingClaimableMinor = $this->remainingClaimableMinor($lockedSession);
            $amountMinor = (int) ($data['amount_minor'] ?? $remainingClaimableMinor);
            if ($amountMinor <= 0 || $amountMinor > $remainingClaimableMinor) {
                throw new LogicException('Claim amount must be positive and cannot exceed the remaining claimable balance.');
            }

            $claim = PaymentClaim::query()->create([
                'company_id' => $lockedSession->company_id,
                'collection_session_id' => $lockedSession->id,
                'method' => $data['method'],
                'amount_minor' => $amountMinor,
                'currency_code' => $lockedSession->currency_code,
                'reference' => $data['reference'] ?? null,
                'status' => 'submitted',
                'payer_name' => $data['payer_name'] ?? null,
                'payer_email' => $data['payer_email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            if ($evidence) {
                $disk = config('titanpay.private_disk', 'local');
                $path = $evidence->store('titanpay/'.$lockedSession->company_id.'/claims/'.$claim->id, $disk);
                $claim->evidence()->create([
                    'company_id' => $lockedSession->company_id,
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $this->sanitizeOriginalName($evidence),
                    'mime_type' => $evidence->getMimeType(),
                    'size_bytes' => $evidence->getSize(),
                    'sha256' => hash_file('sha256', $evidence->getRealPath()),
                    'created_by_user_id' => $this->actorContext->userId(),
                ]);
            }

            $lockedSession->update(['status' => CollectionStatus::AwaitingEvidence]);

            return $claim->load('evidence');
        });
    }

    public function approve(PaymentClaim $claim, int $reviewerId): PaymentClaim
    {
        DB::transaction(function () use ($claim, $reviewerId): void {
            $lockedClaim = PaymentClaim::query()->whereKey($claim->id)->lockForUpdate()->firstOrFail();
            if ($lockedClaim->status !== 'submitted') {
                throw new LogicException('Only submitted payment claims can be approved.');
            }

            $session = CollectionSession::query()->whereKey($lockedClaim->collection_session_id)->lockForUpdate()->firstOrFail();
            if ($session->status === CollectionStatus::Completed) {
                throw new LogicException('The collection session has already been completed.');
            }

            $invoice = $session->invoice_id
                ? Invoice::query()->whereKey($session->invoice_id)->lockForUpdate()->first()
                : null;
            if ($invoice && $lockedClaim->amount_minor > (int) $invoice->balance_due_minor) {
                throw new LogicException('The claim exceeds the invoice balance remaining at review time.');
            }

            $payment = Payment::query()->create([
                'company_id' => $lockedClaim->company_id,
                'payer_type' => 'claim',
                'payer_id' => $lockedClaim->id,
                'payment_method' => $lockedClaim->method,
                'currency_code' => $lockedClaim->currency_code,
                'amount_minor' => $lockedClaim->amount_minor,
                'unallocated_minor' => $lockedClaim->amount_minor,
                'received_at' => now(),
                'reference' => $lockedClaim->reference,
                'status' => PaymentStatus::Verified,
                'verified_at' => now(),
                'metadata_json' => ['claim_id' => $lockedClaim->id],
                'created_by_user_id' => $reviewerId,
            ]);

            if ($invoice) {
                $this->allocations->allocate($payment, $invoice, $lockedClaim->amount_minor);
            }

            $lockedClaim->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $reviewerId,
            ]);

            if ($invoice && $invoice->refresh()->balance_due_minor === 0) {
                $this->completion->complete($session, $invoice);
            } else {
                $session->update(['status' => CollectionStatus::AwaitingEvidence]);
            }
        });

        return $claim->fresh();
    }

    public function reject(PaymentClaim $claim, int $reviewerId, string $reason): PaymentClaim
    {
        DB::transaction(function () use ($claim, $reviewerId, $reason): void {
            $lockedClaim = PaymentClaim::query()->whereKey($claim->id)->lockForUpdate()->firstOrFail();
            if ($lockedClaim->status !== 'submitted') {
                throw new LogicException('Only submitted payment claims can be rejected.');
            }
            $lockedClaim->update([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $reviewerId,
                'rejection_reason' => $reason,
            ]);
        });

        return $claim->fresh();
    }

    private function remainingClaimableMinor(CollectionSession $session): int
    {
        $invoiceBalance = $session->invoice_id
            ? (int) (Invoice::query()->whereKey($session->invoice_id)->lockForUpdate()->value('balance_due_minor') ?? 0)
            : (int) $session->amount_minor;

        $submittedClaimsQuery = PaymentClaim::query()->where('status', 'submitted');
        if ($session->invoice_id) {
            $submittedClaimsQuery->whereHas('session', function ($query) use ($session): void {
                $query->where('company_id', $session->company_id)
                    ->where('invoice_id', $session->invoice_id);
            });
        } else {
            $submittedClaimsQuery->where('collection_session_id', $session->id);
        }

        $submittedClaims = (int) $submittedClaimsQuery->sum('amount_minor');

        return max(0, min((int) $session->amount_minor, $invoiceBalance) - $submittedClaims);
    }

    private function sanitizeOriginalName(UploadedFile $evidence): string
    {
        $name = basename(str_replace('\\', '/', $evidence->getClientOriginalName()));
        $name = preg_replace('/[^\pL\pN._ -]+/u', '_', $name) ?: 'payment-evidence';
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?: $name, " .\t\n\r\0\x0B");

        return mb_substr($name !== '' ? $name : 'payment-evidence', 0, 180);
    }
}
