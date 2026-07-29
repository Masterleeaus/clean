<?php

use App\Domains\TitanMoney\Services\InvoiceCalculator;

it('calculates exclusive Australian GST exactly in minor units', function (): void {
    $result = (new InvoiceCalculator())->calculate([
        ['description'=>'Service','quantity'=>2,'unit_price_minor'=>10000,'discount_minor'=>0,'tax_rate_basis_points'=>1000],
    ]);
    expect($result['subtotal_minor'])->toBe(20000)
        ->and($result['tax_minor'])->toBe(2000)
        ->and($result['total_minor'])->toBe(22000);
});

it('extracts GST from tax inclusive lines', function (): void {
    $result = (new InvoiceCalculator())->calculate([
        ['description'=>'Inclusive service','quantity'=>1,'unit_price_minor'=>11000,'discount_minor'=>0,'tax_rate_basis_points'=>1000],
    ], true);
    expect($result['tax_minor'])->toBe(1000)
        ->and($result['total_minor'])->toBe(11000);
});

it('caps a line discount so it cannot erase another line', function (): void {
    $result = (new InvoiceCalculator())->calculate([
        ['description'=>'A','quantity'=>'1.0000','unit_price_minor'=>100,'discount_minor'=>200,'tax_rate_basis_points'=>0],
        ['description'=>'B','quantity'=>'1.0000','unit_price_minor'=>100,'discount_minor'=>0,'tax_rate_basis_points'=>0],
    ]);

    expect($result['discount_minor'])->toBe(100)
        ->and($result['total_minor'])->toBe(100);
});
