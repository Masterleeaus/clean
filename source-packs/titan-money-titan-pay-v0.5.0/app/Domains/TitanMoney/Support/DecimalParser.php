<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Support;

use InvalidArgumentException;

final class DecimalParser
{
    public static function minorUnits(string|int|float $value): int
    {
        return self::scaledInteger($value, 2);
    }

    public static function basisPoints(string|int|float $value): int
    {
        return self::scaledInteger($value, 2);
    }

    public static function quantity(string|int|float $value): int
    {
        return self::scaledInteger($value, 4);
    }

    public static function scaledInteger(string|int|float $value, int $scale): int
    {
        if ($scale < 0 || $scale > 8) {
            throw new InvalidArgumentException('Decimal scale must be between zero and eight.');
        }

        $normalised = is_float($value)
            ? rtrim(rtrim(sprintf('%.12F', $value), '0'), '.')
            : trim((string) $value);

        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d+))?$/', $normalised, $matches)) {
            throw new InvalidArgumentException('The value must be a plain decimal number.');
        }

        $negative = ($matches[1] ?? '') === '-';
        $whole = ltrim($matches[2], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = $matches[3] ?? '';
        $factor = 10 ** $scale;

        if (strlen($whole) > 15) {
            throw new InvalidArgumentException('The decimal value is too large.');
        }

        $kept = substr(str_pad($fraction, $scale, '0'), 0, $scale);
        $nextDigit = (int) ($fraction[$scale] ?? '0');
        $result = ((int) $whole * $factor) + ($kept === '' ? 0 : (int) $kept);

        if ($nextDigit >= 5) {
            $result++;
        }

        return $negative ? -$result : $result;
    }
}
