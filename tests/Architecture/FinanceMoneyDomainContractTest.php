<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

$files = [
    'Finance/Domain/Money.php',
    'Finance/Domain/InvoiceState.php',
    'Finance/Domain/QuoteState.php',
    'Finance/Domain/InvoiceCalculator.php',
    'Finance/Domain/BalancedJournal.php',
    'Payments/Domain/PaymentMatchScorer.php',
    'TrustAccounting/Domain/TrustLedgerPolicy.php',
];
foreach ($files as $relative) {
    $path = $root.'/app/Domains/WorkCore/System/Modules/'.$relative;
    $expect(is_file($path), "Missing finance domain file {$relative}.");
    if (is_file($path)) require_once $path;
}

use App\Domains\WorkCore\System\Modules\Finance\Domain\{BalancedJournal, InvoiceCalculator, InvoiceState, Money, QuoteState};
use App\Domains\WorkCore\System\Modules\Payments\Domain\PaymentMatchScorer;
use App\Domains\WorkCore\System\Modules\TrustAccounting\Domain\TrustLedgerPolicy;

$aud = new Money(1250, 'AUD');
$expect($aud->minor === 1250 && $aud->currency === 'AUD', 'Money must preserve minor units and currency.');
$expect($aud->add(new Money(250, 'AUD'))->minor === 1500, 'Money addition failed.');
$expect($aud->subtract(new Money(300, 'AUD'))->minor === 950, 'Money subtraction failed.');
$expect($aud->multiplyRatio(3, 2)->minor === 1875, 'Money ratio multiplication must round half up.');
try {
    $aud->add(new Money(1, 'USD'));
    $expect(false, 'Currency mismatch must throw.');
} catch (InvalidArgumentException) {
    $expect(true, 'Currency mismatch rejected.');
}
try {
    new Money(100, 'aud');
    $expect(false, 'Currency codes must be uppercase ISO-style codes.');
} catch (InvalidArgumentException) {
    $expect(true, 'Invalid currency rejected.');
}

$totals = InvoiceCalculator::calculate([
    ['unit_price_minor' => 10000, 'quantity_milli' => 1500, 'discount_minor' => 1000, 'tax_rate_basis_points' => 1000],
    ['unit_price_minor' => 2500, 'quantity_milli' => 2000, 'discount_minor' => 0, 'tax_rate_basis_points' => 0],
], 'AUD');
$expect($totals['subtotal_minor'] === 20000, 'Invoice subtotal must derive from line snapshots.');
$expect($totals['discount_minor'] === 1000, 'Invoice discount total is incorrect.');
$expect($totals['tax_minor'] === 1400, 'Invoice tax total is incorrect.');
$expect($totals['total_minor'] === 20400, 'Invoice total is incorrect.');
$expect($totals['currency'] === 'AUD', 'Invoice currency must be retained.');

$expect(InvoiceState::canTransition('draft', 'issued'), 'Draft invoice should be issuable.');
$expect(! InvoiceState::canTransition('paid', 'issued'), 'Paid invoice must not return to issued.');
$expect(InvoiceState::canTransition('overdue', 'partially_paid'), 'Overdue invoice should accept a partial allocation.');
$expect(InvoiceState::isImmutable('issued'), 'Issued invoice lines must be immutable.');
$expect(! InvoiceState::isImmutable('draft'), 'Draft invoice lines may be edited.');
$expect(QuoteState::canTransition('issued', 'accepted'), 'Issued quote should be acceptable.');
$expect(! QuoteState::canTransition('accepted', 'draft'), 'Accepted quote must not return to draft.');

$journal = BalancedJournal::validate([
    ['account_code' => '1100', 'debit_minor' => 20400, 'credit_minor' => 0, 'currency' => 'AUD'],
    ['account_code' => '4000', 'debit_minor' => 0, 'credit_minor' => 19000, 'currency' => 'AUD'],
    ['account_code' => '2200', 'debit_minor' => 0, 'credit_minor' => 1400, 'currency' => 'AUD'],
]);
$expect($journal['debit_minor'] === 20400 && $journal['credit_minor'] === 20400, 'Balanced journal totals are incorrect.');
try {
    BalancedJournal::validate([
        ['account_code' => '1100', 'debit_minor' => 100, 'credit_minor' => 0, 'currency' => 'AUD'],
        ['account_code' => '4000', 'debit_minor' => 0, 'credit_minor' => 90, 'currency' => 'AUD'],
    ]);
    $expect(false, 'Unbalanced journal must throw.');
} catch (InvalidArgumentException) {
    $expect(true, 'Unbalanced journal rejected.');
}

$score = PaymentMatchScorer::score(
    ['amount_minor' => 20400, 'currency' => 'AUD', 'reference' => 'INV-1007', 'observed_on' => '2026-07-26'],
    ['amount_minor' => 20400, 'currency' => 'AUD', 'reference' => 'inv 1007', 'due_on' => '2026-07-25']
);
$expect($score['score'] >= 90, 'Exact amount/reference match should score highly.');
$expect(in_array('amount_exact', $score['reasons'], true), 'Amount match reason is missing.');
$expect(in_array('reference_exact', $score['reasons'], true), 'Reference match reason is missing.');
$expect(PaymentMatchScorer::score(
    ['amount_minor' => 100, 'currency' => 'USD', 'reference' => 'X', 'observed_on' => '2026-07-26'],
    ['amount_minor' => 100, 'currency' => 'AUD', 'reference' => 'X', 'due_on' => '2026-07-26']
)['score'] === 0, 'Currency mismatch must never auto-match.');

$expect(TrustLedgerPolicy::mayPostReceipt(false, 'trust'), 'Trust receipt should be allowed into a trust account.');
$expect(! TrustLedgerPolicy::mayPostReceipt(true, 'operating'), 'Trust receipt must not enter an operating account.');
$expect(TrustLedgerPolicy::mayReleaseDisbursement(2, 2), 'Required approvals should release a trust disbursement.');
$expect(! TrustLedgerPolicy::mayReleaseDisbursement(1, 2), 'Insufficient approvals must block trust disbursement.');
$expect(TrustLedgerPolicy::correctionMode() === 'reversal_and_replacement', 'Trust corrections must be append-only reversals.');

fwrite(STDOUT, "Finance money domain: {$checks} checks passed.\n");
