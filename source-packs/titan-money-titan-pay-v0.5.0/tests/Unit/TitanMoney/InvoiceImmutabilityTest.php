<?php

use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Models\Invoice;

it('treats issued financial documents as immutable', function (): void {
    $invoice = new Invoice(['status'=>InvoiceStatus::Issued]);
    expect($invoice->isMutable())->toBeFalse();
});
