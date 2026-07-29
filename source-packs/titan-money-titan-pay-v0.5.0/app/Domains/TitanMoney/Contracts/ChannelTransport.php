<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Contracts;

use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Services\DeliveryReceipt;

interface ChannelTransport
{
    public function supports(string $channel): bool;

    public function send(ChannelDelivery $delivery): DeliveryReceipt;
}
