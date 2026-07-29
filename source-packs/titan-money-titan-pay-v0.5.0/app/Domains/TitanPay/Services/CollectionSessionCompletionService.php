<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanPay\Enums\CollectionStatus;
use App\Domains\TitanPay\Models\CollectionSession;

final class CollectionSessionCompletionService
{
    /**
     * Mark the verified collection session complete and revoke stale payment
     * links once the related invoice no longer has an outstanding balance.
     *
     * The caller must invoke this inside the transaction that holds the
     * current session/invoice locks.
     */
    public function complete(CollectionSession $session, ?Invoice $invoice = null): void
    {
        $session->forceFill([
            'status' => CollectionStatus::Completed,
            'completed_at' => $session->completed_at ?? now(),
            'failure_reason' => null,
        ])->save();

        if ($invoice === null || (int) $invoice->fresh()->balance_due_minor > 0) {
            return;
        }

        CollectionSession::query()
            ->where('company_id', $session->company_id)
            ->where('invoice_id', $invoice->id)
            ->where('id', '!=', $session->id)
            ->whereIn('status', [
                CollectionStatus::Pending->value,
                CollectionStatus::Opened->value,
                CollectionStatus::Processing->value,
                CollectionStatus::AwaitingEvidence->value,
            ])
            ->update([
                'status' => CollectionStatus::Cancelled->value,
                'failure_reason' => 'Invoice balance settled through another collection session.',
                'updated_at' => now(),
            ]);
    }
}
