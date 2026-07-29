<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Services;

use App\Domains\TitanPay\Contracts\GatewayAdapter;
use InvalidArgumentException;

final class GatewayRegistry
{
    /** @var array<string,GatewayAdapter> */
    private array $adapters = [];

    public function register(GatewayAdapter $adapter): void { $this->adapters[$adapter->name()] = $adapter; }
    public function get(string $name): GatewayAdapter
    {
        return $this->adapters[$name] ?? throw new InvalidArgumentException("Unknown TitanPay gateway [{$name}].");
    }
    public function names(): array { return array_keys($this->adapters); }
}
