<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Contracts\ChannelTransport;
use LogicException;

final class ChannelTransportRegistry
{
    /** @var array<int,ChannelTransport> */
    private array $transports = [];

    public function register(ChannelTransport $transport): void
    {
        $this->transports[] = $transport;
    }

    public function for(string $channel): ChannelTransport
    {
        foreach ($this->transports as $transport) {
            if ($transport->supports($channel)) {
                return $transport;
            }
        }

        throw new LogicException("No concrete Titan Channels transport is configured for [{$channel}].");
    }
}
