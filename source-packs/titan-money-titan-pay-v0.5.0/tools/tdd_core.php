<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

function expectTrue(bool $condition, string $message): void
{
    global $failures;
    if (! $condition) {
        $failures[] = $message;
    }
}

$decimalPath = $root.'/app/Domains/TitanMoney/Support/DecimalParser.php';
$moneyPath = $root.'/app/Domains/TitanMoney/ValueObjects/Money.php';
$calculatorPath = $root.'/app/Domains/TitanMoney/Services/InvoiceCalculator.php';
$stripeVerifierPath = $root.'/app/Domains/TitanPay/Security/StripeWebhookVerifier.php';

expectTrue(file_exists($decimalPath), 'Decimal parser must exist.');
expectTrue(file_exists($moneyPath), 'Money value object must exist.');
expectTrue(file_exists($calculatorPath), 'InvoiceCalculator must exist.');
expectTrue(file_exists($stripeVerifierPath), 'StripeWebhookVerifier must exist.');

if (file_exists($decimalPath)) {
    require_once $decimalPath;
}

if (file_exists($moneyPath)) {
    require_once $moneyPath;
    $money = new App\Domains\TitanMoney\ValueObjects\Money(12345, 'AUD');
    expectTrue($money->minor() === 12345, 'Money must preserve integer minor units.');
    expectTrue($money->format('en_AU') === '$123.45', 'Money must format AUD correctly.');
    expectTrue($money->add(new App\Domains\TitanMoney\ValueObjects\Money(55, 'AUD'))->minor() === 12400, 'Money addition must be exact.');

    try {
        $money->add(new App\Domains\TitanMoney\ValueObjects\Money(1, 'USD'));
        expectTrue(false, 'Money must reject mixed currencies.');
    } catch (InvalidArgumentException) {
        expectTrue(true, 'Mixed currency rejected.');
    }
}

if (file_exists($calculatorPath)) {
    require_once $calculatorPath;
    $calculator = new App\Domains\TitanMoney\Services\InvoiceCalculator();
    $result = $calculator->calculate([
        ['quantity' => '2.0000', 'unit_price_minor' => 1500, 'discount_minor' => 0, 'tax_rate_basis_points' => 1000],
        ['quantity' => '1.0000', 'unit_price_minor' => 2500, 'discount_minor' => 500, 'tax_rate_basis_points' => 1000],
    ], false);
    expectTrue($result['subtotal_minor'] === 5500, 'Invoice subtotal must use exact minor-unit arithmetic.');
    expectTrue($result['discount_minor'] === 500, 'Invoice discount must be retained.');
    expectTrue($result['tax_minor'] === 500, 'GST must be calculated after discount.');
    expectTrue($result['total_minor'] === 5500, 'Invoice total must equal subtotal minus discount plus tax.');
}

if (file_exists($stripeVerifierPath)) {
    require_once $stripeVerifierPath;
    $payload = '{"id":"evt_123","type":"checkout.session.completed"}';
    $timestamp = time();
    $secret = 'whsec_test';
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    $header = 't='.$timestamp.',v1='.$signature;
    $verifier = new App\Domains\TitanPay\Security\StripeWebhookVerifier(300);
    expectTrue($verifier->verify($payload, $header, $secret), 'Valid Stripe signatures must pass.');
    expectTrue(! $verifier->verify($payload.'x', $header, $secret), 'Tampered Stripe payloads must fail.');
}

if ($failures !== []) {
    fwrite(STDERR, "RED: core tests failed as expected:\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "GREEN: core Titan Money and Titan Pay invariants pass.\n";
