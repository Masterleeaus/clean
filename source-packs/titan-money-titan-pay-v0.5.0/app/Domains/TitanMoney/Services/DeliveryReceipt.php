<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

final readonly class DeliveryReceipt
{
    /** @param array<string,mixed> $raw */
    public function __construct(
        public string $provider,
        public ?string $externalMessageId,
        public string $status = 'sent',
        public array $raw = [],
        public bool $delivered = false,
    ) {}
}
