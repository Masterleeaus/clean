<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Support;

final class PaymentMethodOrder
{
    /** @return array<int,string> */
    public function customerDefault(): array
    {
        return ['payid', 'bank_transfer', 'cash', 'paypal'];
    }

    /** @param array<int,mixed> $methods @return array<int,string> */
    public function normalise(array $methods, bool $includeStripe = false): array
    {
        $requested = array_values(array_unique(array_filter($methods, 'is_string')));
        $priority = $includeStripe
            ? ['payid', 'bank_transfer', 'cash', 'stripe', 'paypal']
            : $this->customerDefault();

        return array_values(array_filter(
            $priority,
            static fn (string $method): bool => in_array($method, $requested, true),
        ));
    }
}
