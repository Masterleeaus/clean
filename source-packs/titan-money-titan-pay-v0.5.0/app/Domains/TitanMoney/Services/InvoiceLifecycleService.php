<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class InvoiceLifecycleService
{
    public function __construct(
        private readonly TitanMoneyAuditService $audit,
        private readonly TitanMoneyOutbox $outbox,
    ) {}

    public function markOverdue(Invoice $invoice): Invoice
    {
        if (! $this->isEligibleForOverdue($invoice)) {
            return $invoice;
        }

        return DB::transaction(function () use ($invoice): Invoice {
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (! $this->isEligibleForOverdue($locked)) {
                return $locked;
            }

            $before = $locked->toArray();
            $locked->forceFill(['status'=>InvoiceStatus::Overdue,'version'=>$locked->version+1])->save();
            $this->audit->record($locked, 'mark_overdue', $before, 'Invoice passed its due date with an outstanding balance.');
            $this->outbox->record('invoice.overdue', $locked, ['due_date'=>$locked->due_date?->toDateString(),'balance_due_minor'=>$locked->balance_due_minor]);

            return $locked;
        });
    }

    private function isEligibleForOverdue(Invoice $invoice): bool
    {
        if (! in_array($invoice->status, [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid], true)) {
            return false;
        }

        if ((int) $invoice->balance_due_minor <= 0 || $invoice->due_date === null) {
            return false;
        }

        return ! $invoice->due_date->isToday() && $invoice->due_date->isPast();
    }
}
