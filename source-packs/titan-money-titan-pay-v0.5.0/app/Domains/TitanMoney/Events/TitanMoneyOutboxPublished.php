<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Events;

use App\Domains\TitanMoney\Models\TitanMoneyOutboxEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TitanMoneyOutboxPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TitanMoneyOutboxEvent $event) {}
}
