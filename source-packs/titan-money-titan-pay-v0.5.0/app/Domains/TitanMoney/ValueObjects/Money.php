<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\ValueObjects;

use App\Domains\TitanMoney\Support\DecimalParser;
use InvalidArgumentException;
use NumberFormatter;

final readonly class Money
{
    public function __construct(private int $minor, private string $currency = 'AUD')
    {
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Currency must be a three-letter ISO code.');
        }
    }

    public function minor(): int
    {
        return $this->minor;
    }

    public function currency(): string
    {
        return $this->currency;
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

    public function multiply(string|int|float $quantity): self
    {
        $scaled = DecimalParser::quantity($quantity);
        return new self((int) round(($this->minor * $scaled) / 10000), $this->currency);
    }

    public function format(string $locale = 'en_AU'): string
    {
        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($this->minor / 100, $this->currency);
            if (is_string($formatted)) {
                return $formatted;
            }
        }

        $symbol = $this->currency === 'AUD' ? '$' : $this->currency.' ';
        return $symbol.number_format($this->minor / 100, 2, '.', ',');
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies.');
        }
    }
}
