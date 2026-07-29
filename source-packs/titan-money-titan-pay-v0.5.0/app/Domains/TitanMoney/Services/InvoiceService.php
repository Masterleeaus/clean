<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Models\Customer;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use LogicException;

final class InvoiceService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly InvoiceCalculator $calculator,
        private readonly InvoiceNumberService $numbers,
        private readonly TitanMoneyAuditService $audit,
        private readonly TitanMoneyOutbox $outbox,
    ) {}

    public function createDraft(array $data, array $lines): Invoice
    {
        return DB::transaction(function () use ($data, $lines): Invoice {
            $company = $this->currentCompany->require();
            $customer = Customer::query()->findOrFail($data['customer_id']);
            $this->validateLineReferences($lines);
            $calculated = $this->calculator->calculate($lines, (bool) ($data['tax_inclusive'] ?? false));

            $invoice = Invoice::query()->create(array_merge($data, $calculated, [
                'company_id' => $company->id,
                'invoice_number' => $data['invoice_number'] ?? $this->numbers->next($data['invoice_series'] ?? 'INV'),
                'invoice_series' => $data['invoice_series'] ?? 'INV',
                'status' => InvoiceStatus::Draft,
                'currency_code' => strtoupper($data['currency_code'] ?? $company->currency_code ?? 'AUD'),
                'customer_snapshot_json' => $customer->only(['id','name','email','phone','business_name','abn']),
                'billing_address_snapshot_json' => $customer->billing_address_json,
                'service_address_snapshot_json' => $customer->service_address_json,
                'business_snapshot_json' => $company->only(['id','name','legal_name','trading_name','abn','gst_registered','email','phone','address_json']),
                'source_idempotency_key' => $data['source_idempotency_key'] ?? null,
                'version' => 1,
                'created_by_user_id' => $this->actorContext->userId(),
                'created_by_agent_id' => $this->actorContext->agentId(),
                'device_id' => $this->actorContext->deviceId(),
                'conversation_id' => $this->actorContext->conversationId(),
                'correlation_id' => $this->actorContext->correlationId(),
                'causation_id' => $this->actorContext->causationId(),
            ]));

            $invoice->lines()->createMany($calculated['lines']);
            $this->audit->record($invoice, 'create', [], 'Invoice draft created');
            $this->outbox->record('invoice.draft_created', $invoice, ['invoice_number' => $invoice->invoice_number]);

            return $invoice->load('lines','customer');
        });
    }

    public function updateDraft(Invoice $invoice, array $data, array $lines): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $lines): Invoice {
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isMutable()) {
                throw new LogicException('Issued financial documents cannot be edited. Use a credit note or void-and-reissue workflow.');
            }

            $before = $locked->toArray();
            $this->validateLineReferences($lines);
            $customer = Customer::query()->findOrFail($data['customer_id'] ?? $locked->customer_id);
            $calculated = $this->calculator->calculate($lines, (bool) ($data['tax_inclusive'] ?? $locked->tax_inclusive));
            $locked->fill(array_merge($data, $calculated, [
                'customer_snapshot_json' => $customer->only(['id','name','email','phone','business_name','abn']),
                'billing_address_snapshot_json' => $customer->billing_address_json,
                'service_address_snapshot_json' => $customer->service_address_json,
                'version' => $locked->version + 1,
            ]))->save();
            $locked->lines()->delete();
            $locked->lines()->createMany($calculated['lines']);
            $this->audit->record($locked, 'update', $before, 'Invoice draft updated');
            $this->outbox->record('invoice.draft_updated', $locked);

            return $locked->fresh(['lines','customer']);
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            // Issuing is idempotent so a controller can safely retry document/reminder work
            // after the financial state committed but a later side effect failed.
            if ($locked->status === InvoiceStatus::Issued) {
                return $locked->fresh(['lines','customer']);
            }
            if (! in_array($locked->status, [InvoiceStatus::Draft, InvoiceStatus::PendingApproval, InvoiceStatus::Approved], true)) {
                throw new LogicException('Only a draft or approved invoice can be issued.');
            }

            $before = $locked->toArray();
            $company = $this->currentCompany->require();
            $customer = Customer::query()->findOrFail($locked->customer_id);
            $locked->forceFill([
                'customer_snapshot_json' => $customer->only(['id','name','email','phone','business_name','abn']),
                'billing_address_snapshot_json' => $customer->billing_address_json,
                'service_address_snapshot_json' => $customer->service_address_json,
                'business_snapshot_json' => $company->only(['id','name','legal_name','trading_name','abn','gst_registered','email','phone','address_json']),
                'status' => InvoiceStatus::Issued,
                'issue_date' => $locked->issue_date ?: today(),
                'issued_at' => now(),
                'balance_due_minor' => max(0, $locked->total_minor - $locked->amount_paid_minor - $locked->amount_credited_minor),
                'version' => $locked->version + 1,
            ])->save();
            $this->audit->record($locked, 'issue', $before, 'Invoice issued');
            $this->outbox->record('invoice.issued', $locked, [
                'total_minor'=>$locked->total_minor,
                'currency_code'=>$locked->currency_code,
            ]);

            return $locked->fresh(['lines','customer']);
        });
    }

    private function validateLineReferences(array $lines): void
    {
        $productIds = collect($lines)->pluck('product_id')->filter()->unique()->values();
        if ($productIds->isEmpty()) return;
        $found = Product::query()->whereKey($productIds)->count();
        if ($found !== $productIds->count()) {
            throw new AuthorizationException('One or more invoice products do not belong to the active company.');
        }
    }

    public function void(Invoice $invoice, string $reason): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason): Invoice {
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($locked->amount_paid_minor > 0 || $locked->amount_credited_minor > 0) {
                throw new LogicException('An invoice with payments or credits cannot be voided. Use refund, credit, or adjustment workflows.');
            }
            if (in_array($locked->status, [InvoiceStatus::Paid, InvoiceStatus::Voided, InvoiceStatus::WrittenOff], true)) {
                throw new LogicException('This invoice can no longer be voided.');
            }

            $before = $locked->toArray();
            $locked->forceFill([
                'status'=>InvoiceStatus::Voided,
                'voided_at'=>now(),
                'internal_notes'=>trim(($locked->internal_notes ? $locked->internal_notes."\n" : '').'VOID: '.$reason),
                'version'=>$locked->version+1,
            ])->save();
            $this->audit->record($locked, 'void', $before, $reason);
            $this->outbox->record('invoice.voided', $locked, ['reason'=>$reason]);

            return $locked->fresh(['lines','customer']);
        });
    }
}
