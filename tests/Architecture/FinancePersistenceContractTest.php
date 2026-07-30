<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$migration = $root.'/database/migrations/2026_07_26_040000_create_tz_finance_payment_trust_tables.php';
$expect(is_file($migration), 'Finance/payment/trust migration is missing.');
$content = is_file($migration) ? file_get_contents($migration) : '';
$tables = [
    'tz_finance_quotes', 'tz_finance_quote_lines', 'tz_finance_invoices', 'tz_finance_invoice_lines',
    'tz_finance_credit_notes', 'tz_finance_credit_note_lines', 'tz_finance_credit_allocations',
    'tz_finance_expenses', 'tz_finance_receivable_allocations', 'tz_finance_accounting_periods',
    'tz_finance_accounts', 'tz_finance_journals', 'tz_finance_journal_lines',
    'tz_payment_provider_connections', 'tz_payment_sessions', 'tz_payment_attempts',
    'tz_payment_observations', 'tz_payment_matches', 'tz_payment_reconciliations',
    'tz_trust_accounts', 'tz_trust_matters', 'tz_trust_receipts', 'tz_trust_allocations',
    'tz_trust_disbursements', 'tz_trust_approvals', 'tz_trust_ledger_entries',
    'tz_trust_reconciliations', 'tz_trust_reconciliation_lines',
];
foreach ($tables as $table) {
    $expect(str_contains($content, "Schema::create('{$table}'"), "Missing table {$table}.");
}
$expect(substr_count($content, "foreignId('company_id')") >= count($tables), 'Every finance/payment/trust table must be company scoped.');
$expect(substr_count($content, "default('AUD')") >= 12, 'Money tables must default to AUD.');
foreach (['subtotal_minor', 'tax_minor', 'total_minor', 'amount_minor', 'debit_minor', 'credit_minor'] as $column) {
    $expect(str_contains($content, "integer('{$column}')") || str_contains($content, "bigInteger('{$column}')"), "Integer money column {$column} is missing.");
}
$expect(! preg_match('/(?:decimal|float|double)\([^\n]*(?:amount|total|tax|fee|balance)/i', $content), 'Money columns must not use floating-point or decimal storage.');
$expect(str_contains($content, "unique(['company_id', 'provider', 'external_event_id']"), 'Provider webhook idempotency constraint is missing.');
$expect(str_contains($content, 'credential_reference'), 'Provider connections must store Vault references.');
$expect(! str_contains($content, 'gateway_secret'), 'Gateway secrets must not be stored in payment tables.');
$expect(str_contains($content, 'reversal_of_entry_id'), 'Trust correction lineage is missing.');
$expect(str_contains($content, 'reversal_of_allocation_id'), 'Receivable correction lineage is missing.');
$expect(str_contains($content, 'required_approvals'), 'Trust dual-control policy is missing.');
$expect(str_contains($content, 'source_folio_charge_id'), 'Accommodation folio conversion lineage is missing.');
$expect((bool) preg_match("/string\\('status'[^\\n]*default\\('draft'\\)/", $content), 'Draft finance lifecycle defaults are missing.');

$contracts = [
    'Finance/Contracts/FinanceRepositoryContract.php' => ['createQuote', 'createInvoice', 'issueInvoice', 'createCreditNote', 'recordExpense', 'allocateReceivable', 'postJournal', 'financeSummary'],
    'Payments/Contracts/PaymentRepositoryContract.php' => ['createSession', 'recordAttempt', 'recordObservation', 'proposeMatches', 'reconcilePayment', 'paymentSummary'],
    'TrustAccounting/Contracts/TrustAccountingRepositoryContract.php' => ['createTrustAccount', 'createMatter', 'recordReceipt', 'allocateReceipt', 'requestDisbursement', 'approveDisbursement', 'releaseDisbursement', 'reverseLedgerEntry', 'trustSummary'],
];
foreach ($contracts as $relative => $methods) {
    $path = $root.'/app/Domains/WorkCore/System/Modules/'.$relative;
    $expect(is_file($path), "Missing repository contract {$relative}.");
    $source = is_file($path) ? file_get_contents($path) : '';
    foreach ($methods as $method) $expect(str_contains($source, "function {$method}"), "Contract method {$method} is missing.");
}

$repositories = [
    'Finance/Repositories/EloquentFinanceRepository.php',
    'Payments/Repositories/EloquentPaymentRepository.php',
    'TrustAccounting/Repositories/EloquentTrustAccountingRepository.php',
];
foreach ($repositories as $relative) {
    $path = $root.'/app/Domains/WorkCore/System/Modules/'.$relative;
    $expect(is_file($path), "Missing repository {$relative}.");
    $source = is_file($path) ? file_get_contents($path) : '';
    $expect(str_contains($source, 'where(\'company_id\', $companyId)'), "Repository {$relative} must scope reads by company.");
    $expect(! str_contains($source, "['company_id']"), "Repository {$relative} must not trust payload company_id.");
}
$trustRepo = is_file($root.'/app/Domains/WorkCore/System/Modules/TrustAccounting/Repositories/EloquentTrustAccountingRepository.php')
    ? file_get_contents($root.'/app/Domains/WorkCore/System/Modules/TrustAccounting/Repositories/EloquentTrustAccountingRepository.php') : '';
$expect(! str_contains($trustRepo, "->update(['amount_minor'"), 'Trust ledger amounts must not be destructively updated.');
$expect(str_contains($trustRepo, 'reversal_of_entry_id'), 'Trust reversal implementation is missing.');

fwrite(STDOUT, "Finance persistence: {$checks} checks passed.\n");
