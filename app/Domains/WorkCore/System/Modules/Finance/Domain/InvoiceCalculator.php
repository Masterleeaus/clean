<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Domain;

use InvalidArgumentException;

final class InvoiceCalculator
{
    /**
     * @param list<array{unit_price_minor:int,quantity_milli?:int,discount_minor?:int,tax_rate_basis_points?:int}> $lines
     * @return array{subtotal_minor:int,discount_minor:int,tax_minor:int,total_minor:int,currency:string}
     */
    public static function calculate(array $lines, string $currency = 'AUD'): array
    {
        new Money(0, $currency);
        $subtotal = 0;
        $discount = 0;
        $tax = 0;

        foreach ($lines as $line) {
            $unit = (int) ($line['unit_price_minor'] ?? 0);
            $quantityMilli = (int) ($line['quantity_milli'] ?? 1000);
            $lineDiscount = (int) ($line['discount_minor'] ?? 0);
            $taxRate = (int) ($line['tax_rate_basis_points'] ?? 0);
            if ($unit < 0 || $quantityMilli <= 0 || $lineDiscount < 0 || $taxRate < 0) {
                throw new InvalidArgumentException('Invoice line values are invalid.');
            }

            $gross = self::roundRatio($unit * $quantityMilli, 1000);
            if ($lineDiscount > $gross) {
                throw new InvalidArgumentException('Invoice line discount exceeds gross amount.');
            }
            $taxable = $gross - $lineDiscount;
            $lineTax = self::roundRatio($taxable * $taxRate, 10000);
            $subtotal += $gross;
            $discount += $lineDiscount;
            $tax += $lineTax;
        }

        return [
            'subtotal_minor' => $subtotal,
            'discount_minor' => $discount,
            'tax_minor' => $tax,
            'total_minor' => $subtotal - $discount + $tax,
            'currency' => $currency,
        ];
    }

    private static function roundRatio(int $numerator, int $denominator): int
    {
        return intdiv($numerator + intdiv($denominator, 2), $denominator);
    }
}
