<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Support\DecimalParser;

final class InvoiceCalculator
{
    /** @param array<int,array<string,mixed>> $lines */
    public function calculate(array $lines, bool $taxInclusive = false): array
    {
        $subtotal = 0;
        $discount = 0;
        $tax = 0;
        $normalised = [];

        foreach ($lines as $index => $line) {
            $quantityScaled = DecimalParser::quantity($line['quantity'] ?? 1);
            $unitPrice = (int) ($line['unit_price_minor'] ?? 0);
            $lineDiscount = max(0, (int) ($line['discount_minor'] ?? 0));
            $rate = max(0, (int) ($line['tax_rate_basis_points'] ?? 0));
            $gross = (int) round(($unitPrice * $quantityScaled) / 10000);
            $lineDiscount = min($lineDiscount, max(0, $gross));
            $taxable = max(0, $gross - $lineDiscount);

            if ($taxInclusive && $rate > 0) {
                $lineTax = (int) round($taxable - ($taxable * 10000 / (10000 + $rate)));
                $lineTotal = $taxable;
            } else {
                $lineTax = (int) round($taxable * $rate / 10000);
                $lineTotal = $taxable + $lineTax;
            }

            $subtotal += $gross;
            $discount += $lineDiscount;
            $tax += $lineTax;
            $normalised[] = array_merge($line, [
                'quantity' => number_format($quantityScaled / 10000, 4, '.', ''),
                'unit_price_minor' => $unitPrice,
                'discount_minor' => $lineDiscount,
                'tax_rate_basis_points' => $rate,
                'tax_minor' => $lineTax,
                'line_total_minor' => $lineTotal,
                'display_order' => (int) ($line['display_order'] ?? $index),
            ]);
        }

        $total = $taxInclusive ? max(0, $subtotal - $discount) : max(0, $subtotal - $discount + $tax);

        return [
            'subtotal_minor' => $subtotal,
            'discount_minor' => $discount,
            'tax_minor' => $tax,
            'total_minor' => $total,
            'balance_due_minor' => $total,
            'lines' => $normalised,
        ];
    }
}
