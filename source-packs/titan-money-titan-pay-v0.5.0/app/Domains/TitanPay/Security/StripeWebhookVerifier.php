<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Security;

final readonly class StripeWebhookVerifier
{
    public function __construct(private int $toleranceSeconds = 300) {}

    public function verify(string $payload, string $signatureHeader, string $secret, ?int $now = null): bool
    {
        if ($secret === '' || $signatureHeader === '') return false;
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 't' && ctype_digit((string) $value)) $timestamp = (int) $value;
            if ($key === 'v1' && is_string($value)) $signatures[] = $value;
        }

        if ($timestamp === null || $signatures === []) return false;
        $now ??= time();
        if (abs($now - $timestamp) > $this->toleranceSeconds) return false;

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) return true;
        }
        return false;
    }
}
