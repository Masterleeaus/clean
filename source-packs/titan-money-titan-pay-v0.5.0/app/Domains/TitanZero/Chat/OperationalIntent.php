<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\Chat;

final readonly class OperationalIntent
{
    public function __construct(
        public string $action,
        public string $jobQuery,
        public bool $sendInvoice = true,
    ) {}
}
