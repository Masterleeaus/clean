<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Enums\PaymentStatus;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Payment;
use Illuminate\Support\Facades\DB;
use LogicException;

final class PaymentAllocationService
{
    public function __construct(
        private readonly TitanMoneyAuditService $audit,
        private readonly TitanMoneyOutbox $outbox,
    ) {}

    public function allocate(Payment $payment, Invoice $invoice, int $amountMinor): void
    {
        if ($amountMinor <= 0) {
            throw new LogicException('Allocation amount must be positive.');
        }

        DB::transaction(function () use ($payment, $invoice, $amountMinor): void {
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $lockedInvoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->company_id !== $lockedInvoice->company_id) {
                throw new LogicException('Payment and invoice must belong to the same company.');
            }
            if ($lockedPayment->currency_code !== $lockedInvoice->currency_code) {
                throw new LogicException('Payment and invoice currency must match.');
            }
            if (! in_array($lockedPayment->status, [PaymentStatus::Verified, PaymentStatus::PartiallyAllocated], true)) {
                throw new LogicException('Only verified payments can be allocated.');
            }
            if (! in_array($lockedInvoice->status, [InvoiceStatus::Issued, InvoiceStatus::Overdue, InvoiceStatus::PartiallyPaid], true)) {
                throw new LogicException('Payments can only be allocated to issued, overdue, or partially paid invoices.');
            }

            $amount = min($amountMinor, $lockedPayment->unallocated_minor, $lockedInvoice->balance_due_minor);
            if ($amount <= 0) {
                throw new LogicException('There is no allocatable balance.');
            }

            $lockedPayment->allocations()->create([
                'invoice_id' => $lockedInvoice->id,
                'amount_minor' => $amount,
                'allocated_at' => now(),
            ]);
            $lockedPayment->unallocated_minor -= $amount;
            $lockedPayment->status = $lockedPayment->unallocated_minor === 0
                ? PaymentStatus::Allocated
                : PaymentStatus::PartiallyAllocated;
            $lockedPayment->save();

            $before = $lockedInvoice->toArray();
            $lockedInvoice->amount_paid_minor += $amount;
            $lockedInvoice->balance_due_minor = max(
                0,
                $lockedInvoice->total_minor - $lockedInvoice->amount_paid_minor - $lockedInvoice->amount_credited_minor,
            );
            if ($lockedInvoice->balance_due_minor === 0) {
                $lockedInvoice->status = InvoiceStatus::Paid;
                $lockedInvoice->paid_at = now();
            } else {
                $lockedInvoice->status = InvoiceStatus::PartiallyPaid;
            }
            $lockedInvoice->version++;
            $lockedInvoice->save();

            $this->audit->record($lockedInvoice, 'payment_allocate', $before, 'Verified payment allocated');
            $this->outbox->record(
                $lockedInvoice->status === InvoiceStatus::Paid ? 'invoice.paid' : 'invoice.partially_paid',
                $lockedInvoice,
                ['payment_id' => $lockedPayment->id, 'amount_minor' => $amount],
            );
        });
    }
}
