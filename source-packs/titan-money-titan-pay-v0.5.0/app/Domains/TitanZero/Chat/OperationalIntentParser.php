<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\Chat;

final class OperationalIntentParser
{
    /**
     * Only affirmative completion statements are operational commands.
     * Questions, intentions and negated statements deliberately fall through
     * to ordinary Titan Zero conversation instead of mutating WorkCore.
     */
    private const COMPLETION_PATTERN = '/(?:\b(?:i|we)(?:[\'’]ve|\s+have)?\s+(?:completed|finished|done|closed\s+out)\b|^\s*(?:completed|finished|done|closed\s+out)\b|\bjob\b.*?\b(?:is|was)\s+(?:complete|completed|finished|done)\b)/ui';

    public function parse(string $message): ?OperationalIntent
    {
        $normalised = trim(preg_replace('/\s+/u', ' ', $message) ?? $message);
        if ($normalised === '' || str_contains($normalised, '?')) {
            return null;
        }

        if (! preg_match(self::COMPLETION_PATTERN, $normalised)) {
            return null;
        }

        if (! preg_match('/\bjob\b/ui', $normalised)) {
            return null;
        }

        $jobQuery = $this->extractReference($normalised) ?? $this->extractDescription($normalised);
        if ($jobQuery === null || $jobQuery === '') {
            return null;
        }

        $sendInvoice = ! preg_match('/\b(?:do not|don\'t|without)\s+(?:send|invoice|email|message)\b/ui', $normalised);

        return new OperationalIntent('complete_job_and_invoice', $jobQuery, $sendInvoice);
    }

    private function extractReference(string $message): ?string
    {
        if (preg_match('/\b(?:job\s*)?([A-Z]{1,8}[-#]?\d{2,})\b/u', $message, $matches)) {
            return strtoupper($matches[1]);
        }

        if (preg_match('/\bjob\s*#?([0-9]{1,})\b/ui', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractDescription(string $message): ?string
    {
        $value = preg_replace(self::COMPLETION_PATTERN, '', $message);
        $value = preg_replace('/\b(?:the|a|an|is|was)\b/ui', ' ', (string) $value);
        $value = preg_replace('/\bjob\b/ui', ' ', (string) $value);
        $value = preg_replace('/[,.;:]?\s*(?:and\s+)?(?:please\s+)?(?:send|email|message|issue|create)\b.*$/ui', '', (string) $value);
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? (string) $value, " \t\n\r\0\x0B,.;:");

        return $value !== '' ? $value : null;
    }
}
