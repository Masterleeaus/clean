<?php

use App\Domains\TitanPay\Security\StripeWebhookVerifier;

it('accepts an authentic current Stripe signature', function (): void {
    $now=1700000000; $payload='{"id":"evt_1"}'; $secret='whsec_test';
    $signature=hash_hmac('sha256',$now.'.'.$payload,$secret);
    expect((new StripeWebhookVerifier(300))->verify($payload,"t={$now},v1={$signature}",$secret,$now))->toBeTrue();
});

it('rejects tampered or stale Stripe events', function (): void {
    $now=1700000000; $payload='{"id":"evt_1"}'; $secret='whsec_test';
    $signature=hash_hmac('sha256',$now.'.'.$payload,$secret);
    $verifier=new StripeWebhookVerifier(300);
    expect($verifier->verify($payload.'x',"t={$now},v1={$signature}",$secret,$now))->toBeFalse()
        ->and($verifier->verify($payload,"t={$now},v1={$signature}",$secret,$now+301))->toBeFalse();
});
