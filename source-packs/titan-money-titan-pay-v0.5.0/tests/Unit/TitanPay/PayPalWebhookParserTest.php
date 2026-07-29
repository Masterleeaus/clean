<?php

use App\Domains\TitanPay\Support\PayPalWebhookParser;

it('maps a completed capture back to the PayPal order and exact AUD minor units', function (): void {
    $parsed = (new PayPalWebhookParser())->parse([
        'id'=>'WH-1',
        'event_type'=>'PAYMENT.CAPTURE.COMPLETED',
        'resource'=>[
            'id'=>'CAPTURE-1',
            'amount'=>['value'=>'12.34','currency_code'=>'AUD'],
            'supplementary_data'=>['related_ids'=>['order_id'=>'ORDER-1']],
        ],
    ]);

    expect($parsed['session_reference'])->toBe('ORDER-1')
        ->and($parsed['transaction_id'])->toBe('CAPTURE-1')
        ->and($parsed['amount_minor'])->toBe(1234)
        ->and($parsed['currency_code'])->toBe('AUD')
        ->and($parsed['status'])->toBe('completed');
});
