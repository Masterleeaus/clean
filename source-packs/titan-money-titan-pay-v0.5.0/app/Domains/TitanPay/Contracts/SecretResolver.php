<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Contracts;

interface SecretResolver
{
    /** @return array<string,mixed> */
    public function resolve(string $reference): array;
}
