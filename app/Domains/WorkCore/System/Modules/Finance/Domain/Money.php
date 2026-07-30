<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Domain;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(public int $minor, public string $currency = 'AUD')
    {
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Currency must be an uppercase ISO 4217 code.');
        }
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minor + $other->minor, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minor - $other->minor, $this->currency);
    }

    public function multiplyRatio(int $numerator, int $denominator): self
    {
        if ($denominator === 0) {
            throw new InvalidArgumentException('Money ratio denominator cannot be zero.');
        }

        $product = $this->minor * $numerator;
        $sign = $product < 0 ? -1 : 1;
        $absolute = abs($product);
        $rounded = intdiv($absolute + intdiv(abs($denominator), 2), abs($denominator));

        return new self($rounded * $sign * ($denominator < 0 ? -1 : 1), $this->currency);
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Money currencies must match.');
        }
    }
}
