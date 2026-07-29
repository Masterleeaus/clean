<?php

use App\Domains\TitanMoney\ValueObjects\Money;
it('stores Australian money in integer minor units', function (): void {
    $money = new Money(12345, 'AUD');
    expect($money->minor())->toBe(12345)
        ->and($money->currency())->toBe('AUD')
        ->and($money->format())->toContain('123.45');
});

it('rejects arithmetic across currencies', function (): void {
    (new Money(100, 'AUD'))->add(new Money(100, 'USD'));
})->throws(\InvalidArgumentException::class);
