<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Domain;

use InvalidArgumentException;

final class BalancedJournal
{
    /**
     * @param list<array{account_code:string,debit_minor:int,credit_minor:int,currency:string}> $lines
     * @return array{debit_minor:int,credit_minor:int,currency:string}
     */
    public static function validate(array $lines): array
    {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('A journal requires at least two lines.');
        }

        $currency = null;
        $debits = 0;
        $credits = 0;
        foreach ($lines as $line) {
            $lineCurrency = (string) ($line['currency'] ?? '');
            new Money(0, $lineCurrency);
            $currency ??= $lineCurrency;
            if ($currency !== $lineCurrency) {
                throw new InvalidArgumentException('Journal lines must share one currency.');
            }
            $debit = (int) ($line['debit_minor'] ?? 0);
            $credit = (int) ($line['credit_minor'] ?? 0);
            if ($debit < 0 || $credit < 0 || ($debit > 0 && $credit > 0) || ($debit === 0 && $credit === 0)) {
                throw new InvalidArgumentException('Each journal line must contain one positive debit or credit.');
            }
            if (trim((string) ($line['account_code'] ?? '')) === '') {
                throw new InvalidArgumentException('Journal account code is required.');
            }
            $debits += $debit;
            $credits += $credit;
        }

        if ($debits === 0 || $debits !== $credits) {
            throw new InvalidArgumentException('Journal debits and credits must balance.');
        }

        return ['debit_minor' => $debits, 'credit_minor' => $credits, 'currency' => (string) $currency];
    }
}
