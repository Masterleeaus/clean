<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Domain;

use DateTimeImmutable;

final class PaymentMatchScorer
{
    /** @return array{score:int,reasons:list<string>} */
    public static function score(array $observation, array $receivable): array
    {
        if (($observation['currency'] ?? null) !== ($receivable['currency'] ?? null)) {
            return ['score' => 0, 'reasons' => ['currency_mismatch']];
        }

        $score = 0;
        $reasons = [];
        if ((int) ($observation['amount_minor'] ?? -1) === (int) ($receivable['amount_minor'] ?? -2)) {
            $score += 55;
            $reasons[] = 'amount_exact';
        }

        $observedReference = self::normaliseReference((string) ($observation['reference'] ?? ''));
        $receivableReference = self::normaliseReference((string) ($receivable['reference'] ?? ''));
        if ($observedReference !== '' && $observedReference === $receivableReference) {
            $score += 35;
            $reasons[] = 'reference_exact';
        } elseif ($observedReference !== '' && $receivableReference !== '' && (str_contains($observedReference, $receivableReference) || str_contains($receivableReference, $observedReference))) {
            $score += 20;
            $reasons[] = 'reference_partial';
        }

        try {
            $observed = new DateTimeImmutable((string) ($observation['observed_on'] ?? ''));
            $due = new DateTimeImmutable((string) ($receivable['due_on'] ?? ''));
            $days = abs((int) $observed->diff($due)->format('%r%a'));
            if ($days <= 3) {
                $score += 10;
                $reasons[] = 'date_close';
            } elseif ($days <= 14) {
                $score += 5;
                $reasons[] = 'date_plausible';
            }
        } catch (\Throwable) {
            // Date evidence is optional; invalid dates contribute no score.
        }

        return ['score' => min(100, $score), 'reasons' => $reasons];
    }

    private static function normaliseReference(string $reference): string
    {
        return strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '', $reference));
    }
}
