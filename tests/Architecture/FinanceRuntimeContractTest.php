<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };

$providers = [
    'Finance' => $root.'/app/Domains/WorkCore/System/Modules/Finance/Providers/WorkFinanceServiceProvider.php',
    'Payments' => $root.'/app/Domains/WorkCore/System/Modules/Payments/Providers/WorkPaymentsServiceProvider.php',
    'TrustAccounting' => $root.'/app/Domains/WorkCore/System/Modules/TrustAccounting/Providers/WorkTrustAccountingServiceProvider.php',
];
foreach ($providers as $name => $path) $expect(is_file($path), "{$name} provider is missing.");

$financeActions = [
    'workcore.finance.quote.create', 'workcore.finance.quote.status', 'workcore.finance.invoice.create',
    'workcore.finance.invoice.issue', 'workcore.finance.invoice.status', 'workcore.finance.credit_note.create',
    'workcore.finance.credit.allocate', 'workcore.finance.expense.record', 'workcore.finance.expense.approve',
    'workcore.finance.receivable.allocate', 'workcore.finance.account.create', 'workcore.finance.period.create',
    'workcore.finance.period.close', 'workcore.finance.journal.post',
];
$paymentActions = [
    'workcore.payment.session.create', 'workcore.payment.attempt.record', 'workcore.payment.observation.record',
    'workcore.payment.matches.propose', 'workcore.payment.reconcile',
];
$trustActions = [
    'workcore.trust.account.create', 'workcore.trust.matter.create', 'workcore.trust.receipt.record',
    'workcore.trust.receipt.allocate', 'workcore.trust.disbursement.request',
    'workcore.trust.disbursement.approve', 'workcore.trust.disbursement.release', 'workcore.trust.ledger.reverse',
];
$financeProvider = is_file($providers['Finance']) ? file_get_contents($providers['Finance']) : '';
$paymentProvider = is_file($providers['Payments']) ? file_get_contents($providers['Payments']) : '';
$trustProvider = is_file($providers['TrustAccounting']) ? file_get_contents($providers['TrustAccounting']) : '';
foreach ($financeActions as $key) $expect(str_contains($financeProvider, "'{$key}'"), "Finance action {$key} is missing.");
foreach ($paymentActions as $key) $expect(str_contains($paymentProvider, "'{$key}'"), "Payment action {$key} is missing.");
foreach ($trustActions as $key) $expect(str_contains($trustProvider, "'{$key}'"), "Trust action {$key} is missing.");

foreach (['workcore.finance.summary','workcore.finance.invoice.profile','workcore.finance.quote.profile','workcore.finance.receivables'] as $key) {
    $expect(str_contains($financeProvider, "'{$key}'"), "Finance read {$key} is missing.");
}
foreach (['workcore.payment.summary','workcore.payment.session'] as $key) $expect(str_contains($paymentProvider, "'{$key}'"), "Payment read {$key} is missing.");
foreach (['workcore.trust.summary','workcore.trust.matter.ledger'] as $key) $expect(str_contains($trustProvider, "'{$key}'"), "Trust read {$key} is missing.");

foreach (['workcore.finance','workcore.payments','workcore.trust_accounting'] as $capability) {
    $expect(str_contains(file_get_contents($root.'/config/workcore.php'), "'{$capability}'"), "Capability {$capability} is missing from config.");
}
$rootProvider = file_get_contents($root.'/app/Domains/WorkCore/WorkCoreServiceProvider.php');
foreach (['WorkFinanceServiceProvider','WorkPaymentsServiceProvider','WorkTrustAccountingServiceProvider'] as $class) {
    $expect(str_contains($rootProvider, $class), "{$class} is not registered by WorkCore.");
}

$expect(str_contains($paymentProvider, "'canonical_owner' => 'ZeroPay Payment Orchestration'"), 'ZeroPay authority metadata is missing.');
$expect(str_contains($financeProvider, "'canonical_owner' => 'WorkCore Finance'"), 'WorkCore Finance authority metadata is missing.');
$expect(str_contains($trustProvider, "'canonical_owner' => 'WorkCore Trust Accounting'"), 'Trust authority metadata is missing.');
$expect(str_contains($trustProvider, "'critical'"), 'Trust release/reversal actions must be critical risk.');
$expect(! str_contains($paymentProvider, 'Http::'), 'Payment provider must not execute remote provider calls.');
$expect(! str_contains($paymentProvider, "table('tz_finance_invoices')"), 'Payment provider must not update invoice tables directly.');
$trustRepository = file_get_contents($root.'/app/Domains/WorkCore/System/Modules/TrustAccounting/Repositories/EloquentTrustAccountingRepository.php');
$expect(str_contains($trustRepository, 'allocation_transfer_out'), 'Unassigned trust receipt allocation must preserve account balance with transfer-out lineage.');
$expect(str_contains($trustRepository, 'allocation_transfer_in'), 'Trust matter allocation must create a matter sub-ledger entry.');
$paymentRepository = file_get_contents($root.'/app/Domains/WorkCore/System/Modules/Payments/Repositories/EloquentPaymentRepository.php');
$financeRepository = file_get_contents($root.'/app/Domains/WorkCore/System/Modules/Finance/Repositories/EloquentFinanceRepository.php');
$expect(! str_contains($paymentRepository, "['session_token' =>"), 'Payment session secrets must not enter governed action results.');
$expect(str_contains($paymentRepository, 'Payment observation amount must be positive.'), 'Payment observations must reject zero or negative money.');
$expect(str_contains($financeRepository, "in_array(\$status, ['partially_paid', 'paid']"), 'Manual invoice transitions must not bypass receivable allocation.');
$expect(str_contains($financeRepository, 'Accounting period is closed.'), 'Journal posting must reject closed accounting periods.');
$expect(str_contains($trustRepository, 'Trust reversal would overdraw the matter.'), 'Trust correction must not silently overdraw a matter.');

$financeContract = file_get_contents($root.'/app/Domains/WorkCore/System/Modules/Finance/Contracts/FinanceRepositoryContract.php');
foreach (['transitionQuote','transitionInvoice','allocateCredit','approveExpense','createAccount','createAccountingPeriod','closeAccountingPeriod'] as $method) {
    $expect(str_contains($financeContract, "function {$method}"), "Finance repository method {$method} is missing.");
}

fwrite(STDOUT, "Finance runtime: {$checks} checks passed.\n");
