<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Issued = 'issued';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Disputed = 'disputed';
    case CollectionHold = 'collection_hold';
    case Voided = 'voided';
    case WrittenOff = 'written_off';
}
