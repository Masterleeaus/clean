<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Events;

use App\Domains\TitanMoney\Models\ChannelDelivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TitanChannelsDeliveryRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ChannelDelivery $delivery) {}
}
