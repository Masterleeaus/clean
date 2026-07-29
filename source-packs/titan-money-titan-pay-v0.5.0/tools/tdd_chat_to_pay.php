<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$required = [
    'app/Domains/TitanZero/Chat/OperationalIntent.php',
    'app/Domains/TitanZero/Chat/OperationalIntentParser.php',
    'app/Domains/TitanPay/Support/PaymentMethodOrder.php',
    'app/Domains/TitanMoney/Services/CustomerChannelPreferenceResolver.php',
];

foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        fwrite(STDERR, "RED: missing {$file}\n");
        exit(1);
    }
    require_once $root.'/'.$file;
}

use App\Domains\TitanMoney\Services\CustomerChannelPreferenceResolver;
use App\Domains\TitanPay\Support\PaymentMethodOrder;
use App\Domains\TitanZero\Chat\OperationalIntentParser;

function same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$message}\nExpected: ".var_export($expected, true)."\nActual: ".var_export($actual, true)."\n");
        exit(1);
    }
}

$parser = new OperationalIntentParser();
$intent = $parser->parse('I completed job TZ-1042, send the invoice to the customer.');
same('complete_job_and_invoice', $intent?->action, 'Completion wording must produce the governed workflow intent.');
same('TZ-1042', $intent?->jobQuery, 'The parser must preserve the explicit job reference.');
same(true, $intent?->sendInvoice, 'Explicit send wording must request customer delivery.');

$intent = $parser->parse('Finished the Smith cleaning job');
same('Smith cleaning', $intent?->jobQuery, 'Natural-language job descriptions must be extracted without filler words.');
same(true, $intent?->sendInvoice, 'Completion commands default to invoice delivery.');

$intent = $parser->parse("I've finished the Jones gardening job");
same('Jones gardening', $intent?->jobQuery, 'Contracted completion wording must be understood.');

$intent = $parser->parse('Job TZ-2048 is complete, send the invoice');
same('TZ-2048', $intent?->jobQuery, 'Job-first completion wording must preserve its reference.');

same(null, $parser->parse('Can you tell me how invoices work?'), 'General invoice questions must fall through to normal AI chat.');
same(null, $parser->parse('The Smith job is not complete.'), 'Negated completion statements must never execute a job action.');
same(null, $parser->parse("I haven't completed job TZ-1042."), 'Unfinished-job statements must never execute a job action.');
same(null, $parser->parse('Is job TZ-1042 complete?'), 'Questions about completion must never execute a job action.');
same(null, $parser->parse('I need to complete job TZ-1042 tomorrow.'), 'Future or intended work must never execute a job action.');

$order = new PaymentMethodOrder();
same(['payid','bank_transfer','cash','paypal'], $order->customerDefault(), 'Customer payment preference order must match the product decision.');
same(['payid','cash','paypal'], $order->normalise(['paypal','cash','stripe','payid'], false), 'Stripe must be excluded from the default customer flow and configured methods must retain product priority.');
same(['payid','cash','stripe','paypal'], $order->normalise(['paypal','cash','stripe','payid'], true), 'Stripe may appear only when the company explicitly enables it.');

$resolver = new CustomerChannelPreferenceResolver();
$customer = [
    'metadata_json' => [
        'customer_app_user_id' => 22,
        'communication_preferences' => [
            'customer_app' => true,
            'sms' => true,
            'email' => true,
            'voice' => false,
        ],
    ],
    'phone' => '0400000000',
    'email' => 'customer@example.com',
    'external_id' => 'workcore-customer-22',
];
same(['customer_app','sms','email'], $resolver->resolve($customer, ['customer_app','sms','email','voice']), 'Customer app must be preferred, followed by SMS and email.');

$customer['metadata_json']['communication_preferences']['customer_app'] = false;
same(['sms','email'], $resolver->resolve($customer, ['customer_app','sms','email']), 'Disabled channels must be removed without losing fallback channels.');

fwrite(STDOUT, "GREEN: chat intent, payment order and customer channel preference invariants passed.\n");
