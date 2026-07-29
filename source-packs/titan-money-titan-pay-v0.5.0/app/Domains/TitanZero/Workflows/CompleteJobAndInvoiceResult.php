<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\Workflows;

final readonly class CompleteJobAndInvoiceResult
{
    /** @param array<string,mixed> $metadata */
    public function __construct(
        public bool $success,
        public string $message,
        public array $metadata = [],
        public int $httpStatus = 200,
    ) {}
}
