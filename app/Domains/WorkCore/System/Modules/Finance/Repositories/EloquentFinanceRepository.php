<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Repositories;

use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use App\Domains\WorkCore\System\Modules\Finance\Domain\{BalancedJournal, InvoiceCalculator, InvoiceState, QuoteState};
use App\Domains\WorkCore\System\Persistence\TenantScopedRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class EloquentFinanceRepository extends TenantScopedRepository implements FinanceRepositoryContract
{
    public function __construct(ConnectionInterface $db, TenantContextContract $tenant)
    {
        parent::__construct($db, $tenant);
    }

    public function createQuote(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $lines = $this->lines($data);
        $currency = $this->currency($data);
        $totals = InvoiceCalculator::calculate($lines, $currency);

        return $this->db->transaction(function () use ($data, $lines, $currency, $totals, $companyId, $actorId): array {
            $publicId = (string) Str::ulid();
            $number = trim((string) ($data['quote_number'] ?? '')) ?: 'Q-'.now()->format('ymd').'-'.Str::upper(substr($publicId, -6));
            $quoteId = $this->db->table('tz_finance_quotes')->insertGetId([
                'public_id' => $publicId,
                'company_id' => $companyId,
                'customer_id' => $this->optionalId('tz_customers', $companyId, $data['customer_public_id'] ?? null),
                'premises_id' => $this->optionalId('tz_premises', $companyId, $data['premises_public_id'] ?? null),
                'quote_number' => $number,
                'status' => 'draft',
                'currency' => $currency,
                'subtotal_minor' => $totals['subtotal_minor'],
                'discount_minor' => $totals['discount_minor'],
                'tax_minor' => $totals['tax_minor'],
                'total_minor' => $totals['total_minor'],
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $this->json($data['metadata'] ?? null),
                'created_by_user_id' => $actorId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->insertLines('tz_finance_quote_lines', 'quote_id', $quoteId, $companyId, $lines, $currency);
            return $this->snapshot('tz_finance_quotes', $companyId, $publicId);
        }, 3);
    }


    public function transitionQuote(string $publicId, string $status, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($publicId, $status, $data, $companyId): array {
            $quote = $this->locked('tz_finance_quotes', $companyId, $publicId);
            if (! QuoteState::canTransition((string) $quote->status, $status)) {
                throw new InvalidArgumentException('Quote transition is not permitted.');
            }
            $changes = ['status' => $status, 'updated_at' => now()];
            if ($status === 'issued') $changes['issued_at'] = $data['issued_at'] ?? now();
            if ($status === 'accepted') $changes['accepted_at'] = $data['accepted_at'] ?? now();
            if ($status === 'rejected') $changes['rejected_at'] = $data['rejected_at'] ?? now();
            $this->db->table('tz_finance_quotes')->where('id', $quote->id)->update($changes);
            return $this->quoteProfile($publicId, $companyId);
        }, 3);
    }

    public function createInvoice(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $lines = $this->lines($data);
        $currency = $this->currency($data);
        $totals = InvoiceCalculator::calculate($lines, $currency);

        return $this->db->transaction(function () use ($data, $lines, $currency, $totals, $companyId, $actorId): array {
            $publicId = (string) Str::ulid();
            $number = trim((string) ($data['invoice_number'] ?? '')) ?: 'INV-'.now()->format('ymd').'-'.Str::upper(substr($publicId, -6));
            $invoiceId = $this->db->table('tz_finance_invoices')->insertGetId([
                'public_id' => $publicId,
                'company_id' => $companyId,
                'customer_id' => $this->optionalId('tz_customers', $companyId, $data['customer_public_id'] ?? null),
                'premises_id' => $this->optionalId('tz_premises', $companyId, $data['premises_public_id'] ?? null),
                'quote_id' => $this->optionalId('tz_finance_quotes', $companyId, $data['quote_public_id'] ?? null),
                'reservation_id' => $this->optionalId('tz_accommodation_reservations', $companyId, $data['reservation_public_id'] ?? null),
                'source_folio_charge_id' => $this->optionalId('tz_accommodation_charges', $companyId, $data['source_folio_charge_public_id'] ?? null),
                'invoice_number' => $number,
                'status' => 'draft',
                'currency' => $currency,
                'subtotal_minor' => $totals['subtotal_minor'],
                'discount_minor' => $totals['discount_minor'],
                'tax_minor' => $totals['tax_minor'],
                'total_minor' => $totals['total_minor'],
                'allocated_minor' => 0,
                'balance_minor' => $totals['total_minor'],
                'issue_date' => $data['issue_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'billing_snapshot' => $this->json($data['billing_snapshot'] ?? null),
                'metadata' => $this->json($data['metadata'] ?? null),
                'created_by_user_id' => $actorId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->insertLines('tz_finance_invoice_lines', 'invoice_id', $invoiceId, $companyId, $lines, $currency, true);
            return $this->invoiceProfile($publicId, $companyId);
        }, 3);
    }

    public function issueInvoice(string $publicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($publicId, $data, $companyId): array {
            $invoice = $this->locked('tz_finance_invoices', $companyId, $publicId);
            if (! InvoiceState::canTransition((string) $invoice->status, 'issued')) {
                throw new InvalidArgumentException('Invoice cannot be issued from its current state.');
            }
            $this->db->table('tz_finance_invoices')->where('id', $invoice->id)->update([
                'status' => 'issued',
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'issued_at' => $data['issued_at'] ?? now(),
                'updated_at' => now(),
            ]);
            return $this->invoiceProfile($publicId, $companyId);
        }, 3);
    }


    public function transitionInvoice(string $publicId, string $status, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($publicId, $status, $data, $companyId): array {
            $invoice = $this->locked('tz_finance_invoices', $companyId, $publicId);
            if (in_array($status, ['partially_paid', 'paid'], true)) {
                throw new InvalidArgumentException('Paid states are controlled by receivable allocation.');
            }
            if (! InvoiceState::canTransition((string) $invoice->status, $status)) {
                throw new InvalidArgumentException('Invoice transition is not permitted.');
            }
            $changes = ['status' => $status, 'updated_at' => now()];
            if ($status === 'viewed') $changes['viewed_at'] = $data['viewed_at'] ?? now();
            if ($status === 'paid') $changes['paid_at'] = $data['paid_at'] ?? now();
            if ($status === 'void') $changes['voided_at'] = $data['voided_at'] ?? now();
            $this->db->table('tz_finance_invoices')->where('id', $invoice->id)->update($changes);
            return $this->invoiceProfile($publicId, $companyId);
        }, 3);
    }

    public function createCreditNote(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $lines = $this->lines($data);
        $currency = $this->currency($data);
        $totals = InvoiceCalculator::calculate($lines, $currency);
        return $this->db->transaction(function () use ($data, $lines, $currency, $totals, $companyId, $actorId): array {
            $publicId = (string) Str::ulid();
            $number = trim((string) ($data['credit_number'] ?? '')) ?: 'CN-'.now()->format('ymd').'-'.Str::upper(substr($publicId, -6));
            $id = $this->db->table('tz_finance_credit_notes')->insertGetId([
                'public_id' => $publicId, 'company_id' => $companyId,
                'customer_id' => $this->optionalId('tz_customers', $companyId, $data['customer_public_id'] ?? null),
                'invoice_id' => $this->optionalId('tz_finance_invoices', $companyId, $data['invoice_public_id'] ?? null),
                'credit_number' => $number, 'status' => 'draft', 'currency' => $currency,
                'subtotal_minor' => $totals['subtotal_minor'] - $totals['discount_minor'],
                'tax_minor' => $totals['tax_minor'], 'total_minor' => $totals['total_minor'], 'allocated_minor' => 0,
                'issue_date' => $data['issue_date'] ?? null, 'reason' => $data['reason'] ?? null,
                'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->insertLines('tz_finance_credit_note_lines', 'credit_note_id', $id, $companyId, $lines, $currency);
            return $this->snapshot('tz_finance_credit_notes', $companyId, $publicId);
        }, 3);
    }


    public function allocateCredit(string $creditPublicId, string $invoicePublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($creditPublicId, $invoicePublicId, $data, $companyId, $actorId): array {
            $credit = $this->locked('tz_finance_credit_notes', $companyId, $creditPublicId);
            $invoice = $this->locked('tz_finance_invoices', $companyId, $invoicePublicId);
            if ($credit->currency !== $invoice->currency) throw new InvalidArgumentException('Credit currency mismatch.');
            $available = (int) $credit->total_minor - (int) $credit->allocated_minor;
            $amount = (int) ($data['amount_minor'] ?? 0);
            $reversalId = $this->optionalId('tz_finance_credit_allocations', $companyId, $data['reversal_of_allocation_public_id'] ?? null);
            if ($reversalId !== null) $amount = -abs($amount);
            if ($amount === 0 || ($amount > 0 && ($amount > $available || $amount > (int) $invoice->balance_minor))) {
                throw new InvalidArgumentException('Credit allocation amount is invalid.');
            }
            $publicId = (string) Str::ulid();
            $this->db->table('tz_finance_credit_allocations')->insert([
                'public_id' => $publicId, 'company_id' => $companyId, 'credit_note_id' => $credit->id,
                'invoice_id' => $invoice->id, 'amount_minor' => $amount, 'currency' => $credit->currency,
                'reversal_of_allocation_id' => $reversalId, 'allocated_at' => $data['allocated_at'] ?? now(),
                'reason' => $data['reason'] ?? null, 'created_by_user_id' => $actorId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $creditAllocated = (int) $this->db->table('tz_finance_credit_allocations')->where('company_id', $companyId)->where('credit_note_id', $credit->id)->sum('amount_minor');
            $this->db->table('tz_finance_credit_notes')->where('id', $credit->id)->update([
                'allocated_minor' => $creditAllocated, 'status' => $creditAllocated >= (int) $credit->total_minor ? 'allocated' : 'issued', 'updated_at' => now(),
            ]);
            return $this->allocateReceivable($invoicePublicId, [
                'amount_minor' => $amount,
                'currency' => $invoice->currency,
                'source_type' => 'credit_note',
                'source_public_id' => $creditPublicId,
                'reason' => $data['reason'] ?? 'Credit note allocation',
            ], $companyId, $actorId);
        }, 3);
    }

    public function recordExpense(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $currency = $this->currency($data);
        $subtotal = max(0, (int) ($data['subtotal_minor'] ?? 0));
        $tax = max(0, (int) ($data['tax_minor'] ?? 0));
        $total = $subtotal + $tax;
        if ($total <= 0) throw new InvalidArgumentException('Expense total must be positive.');
        $publicId = (string) Str::ulid();
        $number = trim((string) ($data['expense_number'] ?? '')) ?: 'EXP-'.now()->format('ymd').'-'.Str::upper(substr($publicId, -6));
        $this->db->table('tz_finance_expenses')->insert([
            'public_id' => $publicId, 'company_id' => $companyId,
            'supplier_id' => $this->optionalId('tz_suppliers', $companyId, $data['supplier_public_id'] ?? null),
            'premises_id' => $this->optionalId('tz_premises', $companyId, $data['premises_public_id'] ?? null),
            'work_order_id' => $this->optionalId('tz_work_orders', $companyId, $data['work_order_public_id'] ?? null),
            'expense_number' => $number, 'status' => 'draft', 'category' => $data['category'] ?? null,
            'currency' => $currency, 'subtotal_minor' => $subtotal, 'tax_minor' => $tax, 'total_minor' => $total,
            'incurred_on' => $data['incurred_on'] ?? now()->toDateString(), 'due_on' => $data['due_on'] ?? null,
            'description' => $data['description'] ?? null, 'receipt_evidence' => $this->json($data['receipt_evidence'] ?? null),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_finance_expenses', $companyId, $publicId);
    }


    public function approveExpense(string $publicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $expense = $this->locked('tz_finance_expenses', $companyId, $publicId);
        if ((string) $expense->status !== 'draft') throw new InvalidArgumentException('Only draft expenses may be approved.');
        $this->db->table('tz_finance_expenses')->where('id', $expense->id)->update([
            'status' => 'approved', 'approved_by_user_id' => $actorId,
            'approved_at' => $data['approved_at'] ?? now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_finance_expenses', $companyId, $publicId);
    }

    public function allocateReceivable(string $invoicePublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($invoicePublicId, $data, $companyId, $actorId): array {
            $invoice = $this->locked('tz_finance_invoices', $companyId, $invoicePublicId);
            $amount = (int) ($data['amount_minor'] ?? 0);
            $reversalId = $this->optionalId('tz_finance_receivable_allocations', $companyId, $data['reversal_of_allocation_public_id'] ?? null);
            if ($reversalId !== null) $amount = -abs($amount);
            if ($amount === 0 || ($amount > 0 && $amount > (int) $invoice->balance_minor)) {
                throw new InvalidArgumentException('Receivable allocation amount is invalid.');
            }
            if (($data['currency'] ?? $invoice->currency) !== $invoice->currency) throw new InvalidArgumentException('Allocation currency mismatch.');
            $publicId = (string) Str::ulid();
            $this->db->table('tz_finance_receivable_allocations')->insert([
                'public_id' => $publicId, 'company_id' => $companyId, 'invoice_id' => $invoice->id,
                'source_type' => $data['source_type'] ?? 'manual', 'source_public_id' => $data['source_public_id'] ?? null,
                'amount_minor' => $amount, 'currency' => $invoice->currency, 'reversal_of_allocation_id' => $reversalId,
                'allocated_at' => $data['allocated_at'] ?? now(), 'reason' => $data['reason'] ?? null,
                'created_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $allocated = (int) $this->db->table('tz_finance_receivable_allocations')->where('company_id', $companyId)
                ->where('invoice_id', $invoice->id)->sum('amount_minor');
            $balance = max(0, (int) $invoice->total_minor - $allocated);
            $status = $balance === 0 ? 'paid' : ($allocated > 0 ? 'partially_paid' : (string) $invoice->status);
            $this->db->table('tz_finance_invoices')->where('id', $invoice->id)->update([
                'allocated_minor' => $allocated, 'balance_minor' => $balance, 'status' => $status,
                'paid_at' => $balance === 0 ? now() : null, 'updated_at' => now(),
            ]);
            return ['allocation' => $this->snapshot('tz_finance_receivable_allocations', $companyId, $publicId), 'invoice' => $this->invoiceProfile($invoicePublicId, $companyId)];
        }, 3);
    }


    public function createAccount(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $type = strtolower(trim((string) ($data['type'] ?? '')));
        if ($code === '' || $name === '' || ! in_array($type, ['asset','liability','equity','income','expense'], true)) {
            throw new InvalidArgumentException('Valid account code, name and type are required.');
        }
        $publicId = (string) Str::ulid();
        $this->db->table('tz_finance_accounts')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'code' => $code, 'name' => $name,
            'type' => $type, 'currency' => $this->currency($data), 'active' => (bool) ($data['active'] ?? true),
            'is_control_account' => (bool) ($data['is_control_account'] ?? false),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_finance_accounts', $companyId, $publicId);
    }

    public function createAccountingPeriod(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $starts = (string) ($data['starts_on'] ?? ''); $ends = (string) ($data['ends_on'] ?? '');
        if ($starts === '' || $ends === '' || $starts > $ends) throw new InvalidArgumentException('Valid accounting period dates are required.');
        $publicId = (string) Str::ulid();
        $this->db->table('tz_finance_accounting_periods')->insert([
            'public_id' => $publicId, 'company_id' => $companyId,
            'name' => trim((string) ($data['name'] ?? 'Accounting period')),
            'starts_on' => $starts, 'ends_on' => $ends, 'status' => 'open',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_finance_accounting_periods', $companyId, $publicId);
    }

    public function closeAccountingPeriod(string $publicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $period = $this->locked('tz_finance_accounting_periods', $companyId, $publicId);
        if ((string) $period->status !== 'open') throw new InvalidArgumentException('Only open periods may be closed.');
        $this->db->table('tz_finance_accounting_periods')->where('id', $period->id)->update([
            'status' => 'closed', 'closed_at' => $data['closed_at'] ?? now(),
            'closed_by_user_id' => $actorId, 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_finance_accounting_periods', $companyId, $publicId);
    }

    public function postJournal(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $lines = $data['lines'] ?? [];
        if (! is_array($lines)) throw new InvalidArgumentException('Journal lines are required.');
        $totals = BalancedJournal::validate($lines);
        return $this->db->transaction(function () use ($data, $lines, $totals, $companyId, $actorId): array {
            $publicId = (string) Str::ulid();
            $number = trim((string) ($data['journal_number'] ?? '')) ?: 'JRN-'.now()->format('ymdHis').'-'.Str::upper(substr($publicId, -4));
            $periodId = null;
            if (is_string($data['accounting_period_public_id'] ?? null) && trim((string) $data['accounting_period_public_id']) !== '') {
                $period = $this->record('tz_finance_accounting_periods', $companyId, (string) $data['accounting_period_public_id']);
                if ((string) $period->status !== 'open') throw new InvalidArgumentException('Accounting period is closed.');
                $periodId = (int) $period->id;
            }
            $journalId = $this->db->table('tz_finance_journals')->insertGetId([
                'public_id' => $publicId, 'company_id' => $companyId,
                'accounting_period_id' => $periodId,
                'journal_number' => $number, 'source_type' => $data['source_type'] ?? 'manual',
                'source_public_id' => $data['source_public_id'] ?? null, 'status' => 'posted', 'currency' => $totals['currency'],
                'debit_minor' => $totals['debit_minor'], 'credit_minor' => $totals['credit_minor'],
                'posted_at' => $data['posted_at'] ?? now(), 'description' => $data['description'] ?? null,
                'reversal_of_journal_id' => $this->optionalId('tz_finance_journals', $companyId, $data['reversal_of_journal_public_id'] ?? null),
                'posted_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (array_values($lines) as $index => $line) {
                $account = $this->db->table('tz_finance_accounts')->where('company_id', $companyId)
                    ->where('code', (string) ($line['account_code'] ?? ''))->where('active', true)->first();
                if (! $account) throw new InvalidArgumentException('Journal account does not exist or is inactive.');
                $this->db->table('tz_finance_journal_lines')->insert([
                    'company_id' => $companyId, 'journal_id' => $journalId, 'account_id' => $account->id,
                    'position' => $index + 1, 'debit_minor' => (int) ($line['debit_minor'] ?? 0),
                    'credit_minor' => (int) ($line['credit_minor'] ?? 0), 'currency' => $totals['currency'],
                    'memo' => $line['memo'] ?? null, 'dimensions' => $this->json($line['dimensions'] ?? null),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return $this->snapshot('tz_finance_journals', $companyId, $publicId);
        }, 3);
    }

    public function financeSummary(int $companyId, array $filters = []): array
    {
        $this->assertCompany($companyId);
        $invoices = $this->db->table('tz_finance_invoices')->where('company_id', $companyId)->whereNull('deleted_at');
        return [
            'quotes' => $this->db->table('tz_finance_quotes')->where('company_id', $companyId)->whereNull('deleted_at')->count(),
            'invoices' => (clone $invoices)->count(),
            'open_receivables_minor' => (int) (clone $invoices)->whereIn('status', ['issued', 'viewed', 'overdue', 'partially_paid'])->sum('balance_minor'),
            'overdue_invoices' => (clone $invoices)->where('status', 'overdue')->count(),
            'draft_expenses_minor' => (int) $this->db->table('tz_finance_expenses')->where('company_id', $companyId)->where('status', 'draft')->whereNull('deleted_at')->sum('total_minor'),
            'currency' => (string) ($filters['currency'] ?? 'AUD'),
        ];
    }

    public function invoiceProfile(string $publicId, int $companyId): array
    {
        $this->assertCompany($companyId);
        $invoice = $this->record('tz_finance_invoices', $companyId, $publicId);
        return [
            'invoice' => (array) $invoice,
            'lines' => $this->rows('tz_finance_invoice_lines', $companyId, 'invoice_id', (int) $invoice->id),
            'allocations' => $this->rows('tz_finance_receivable_allocations', $companyId, 'invoice_id', (int) $invoice->id),
        ];
    }

    public function quoteProfile(string $publicId, int $companyId): array
    {
        $this->assertCompany($companyId);
        $quote = $this->record('tz_finance_quotes', $companyId, $publicId);
        return ['quote' => (array) $quote, 'lines' => $this->rows('tz_finance_quote_lines', $companyId, 'quote_id', (int) $quote->id)];
    }

    public function receivables(int $companyId, array $filters = []): array
    {
        $this->assertCompany($companyId);
        $limit = min(100, max(1, (int) ($filters['limit'] ?? 50)));
        $query = $this->db->table('tz_finance_invoices')->where('company_id', $companyId)->whereNull('deleted_at')
            ->whereIn('status', ['issued', 'viewed', 'overdue', 'partially_paid'])->orderBy('due_date');
        if (isset($filters['status'])) $query->where('status', (string) $filters['status']);
        return array_map(static fn ($row) => (array) $row, $query->limit($limit)->get()->all());
    }

    private function insertLines(string $table, string $foreignKey, int $parentId, int $companyId, array $lines, string $currency, bool $invoice = false): void
    {
        foreach (array_values($lines) as $index => $line) {
            $calculated = InvoiceCalculator::calculate([$line], $currency);
            $record = [
                'company_id' => $companyId, $foreignKey => $parentId, 'position' => $index + 1,
                'description' => trim((string) ($line['description'] ?? 'Line '.($index + 1))),
                'unit_price_minor' => (int) $line['unit_price_minor'], 'quantity_milli' => (int) ($line['quantity_milli'] ?? 1000),
                'tax_rate_basis_points' => (int) ($line['tax_rate_basis_points'] ?? 0),
                'subtotal_minor' => $calculated['subtotal_minor'] - $calculated['discount_minor'],
                'tax_minor' => $calculated['tax_minor'], 'total_minor' => $calculated['total_minor'],
                'currency' => $currency, 'created_at' => now(), 'updated_at' => now(),
            ];
            if ($table !== 'tz_finance_credit_note_lines') $record['discount_minor'] = (int) ($line['discount_minor'] ?? 0);
            if ($invoice) $record['source_snapshot'] = $this->json($line['source_snapshot'] ?? null);
            elseif ($table === 'tz_finance_quote_lines') $record['metadata'] = $this->json($line['metadata'] ?? null);
            $this->db->table($table)->insert($record);
        }
    }

    private function lines(array $data): array
    {
        $lines = $data['lines'] ?? null;
        if (! is_array($lines) || $lines === []) throw new InvalidArgumentException('At least one finance line is required.');
        return array_values($lines);
    }

    private function currency(array $data): string
    {
        $currency = strtoupper(trim((string) ($data['currency'] ?? 'AUD')));
        if (! preg_match('/^[A-Z]{3}$/', $currency)) throw new InvalidArgumentException('Invalid currency.');
        return $currency;
    }

    private function guard(int $companyId, int $actorId): void
    {
        $this->assertCompany($companyId);
        if ($actorId !== $this->actorId()) throw new InvalidArgumentException('Actor does not match active WorkCore user.');
    }

    private function optionalId(string $table, int $companyId, mixed $publicId): ?int
    {
        if (! is_string($publicId) || trim($publicId) === '') return null;
        return (int) $this->record($table, $companyId, $publicId)->id;
    }

    private function record(string $table, int $companyId, string $publicId): object
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->first();
        if (! $record) throw new InvalidArgumentException('Referenced finance record was not found.');
        return $record;
    }

    private function locked(string $table, int $companyId, string $publicId): object
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->lockForUpdate()->first();
        if (! $record) throw new InvalidArgumentException('Finance record was not found.');
        return $record;
    }

    private function snapshot(string $table, int $companyId, string $publicId): array
    {
        return (array) $this->record($table, $companyId, $publicId);
    }

    private function rows(string $table, int $companyId, string $foreignKey, int $foreignId): array
    {
        return array_map(static fn ($row) => (array) $row, $this->db->table($table)->where('company_id', $companyId)->where($foreignKey, $foreignId)->orderBy('id')->get()->all());
    }

    private function json(mixed $value): ?string
    {
        if ($value === null) return null;
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
