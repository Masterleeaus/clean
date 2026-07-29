<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\ValueObjects;

final readonly class GatewayInitiation
{
    public function __construct(
        public string $status,
        public string $reference,
        public ?string $redirectUrl = null,
        public array $instructions = [],
        public array $raw = [],
    ) {}
}
