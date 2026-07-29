<?php

use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanPay\Enums\CollectionStatus;
use App\Domains\TitanPay\Policies\CollectionEligibility;

it('only collects invoices in receivable states', function (): void {
    $policy = new CollectionEligibility();

    expect($policy->canCreateForInvoice(InvoiceStatus::Issued))->toBeTrue()
        ->and($policy->canCreateForInvoice(InvoiceStatus::Overdue))->toBeTrue()
        ->and($policy->canCreateForInvoice(InvoiceStatus::Draft))->toBeFalse()
        ->and($policy->canCreateForInvoice(InvoiceStatus::Voided))->toBeFalse();
});

it('rejects terminal collection sessions', function (): void {
    $policy = new CollectionEligibility();

    expect($policy->canInitiate(CollectionStatus::Completed))->toBeFalse()
        ->and($policy->canSubmitClaim(CollectionStatus::Expired))->toBeFalse()
        ->and($policy->canSubmitClaim(CollectionStatus::Opened))->toBeTrue();
});
