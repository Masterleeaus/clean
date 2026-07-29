<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Policies;

use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanPay\Enums\CollectionStatus;

final class CollectionEligibility
{
    public function canCreateForInvoice(InvoiceStatus $status): bool
    {
        return in_array($status, [
            InvoiceStatus::Issued,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Overdue,
        ], true);
    }

    public function canInitiate(CollectionStatus $status): bool
    {
        return in_array($status, [
            CollectionStatus::Pending,
            CollectionStatus::Opened,
            CollectionStatus::Processing,
            CollectionStatus::AwaitingEvidence,
        ], true);
    }

    public function canSubmitClaim(CollectionStatus $status): bool
    {
        return in_array($status, [
            CollectionStatus::Pending,
            CollectionStatus::Opened,
            CollectionStatus::Processing,
            CollectionStatus::AwaitingEvidence,
        ], true);
    }
}
