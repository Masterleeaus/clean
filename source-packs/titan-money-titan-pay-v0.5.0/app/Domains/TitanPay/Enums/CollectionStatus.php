<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Enums;

enum CollectionStatus: string
{
    case Pending = 'pending';
    case Opened = 'opened';
    case Processing = 'processing';
    case AwaitingEvidence = 'awaiting_evidence';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
