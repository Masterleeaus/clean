<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Enums;

enum PaymentStatus: string
{
    case Claimed = 'claimed';
    case Pending = 'pending';
    case Verified = 'verified';
    case PartiallyAllocated = 'partially_allocated';
    case Allocated = 'allocated';
    case Failed = 'failed';
    case Reversed = 'reversed';
    case Refunded = 'refunded';
    case Disputed = 'disputed';
    case Chargeback = 'chargeback';
}
