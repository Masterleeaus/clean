<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\ValueObjects;

final readonly class WebhookVerification
{
    public function __construct(
        public bool $valid,
        public string $eventId,
        public string $eventType,
        public ?string $sessionReference = null,
        public ?string $transactionId = null,
        public ?int $amountMinor = null,
        public ?string $currencyCode = null,
        public string $status = 'ignored',
        public array $payload = [],
        public ?string $failureReason = null,
    ) {}
}
