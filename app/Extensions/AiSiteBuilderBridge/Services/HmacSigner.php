<?php

declare(strict_types=1);

namespace App\Extensions\AiSiteBuilderBridge\Services;

use Illuminate\Http\Request;
use RuntimeException;

final class HmacSigner
{
    public function sign(string $method, string $path, string $body, string $timestamp, string $nonce, string $secret): string
    {
        if ($secret === '') {
            throw new RuntimeException('AI site builder signing secret is not configured.');
        }

        return base64_encode(hash_hmac('sha256', $this->canonical($method, $path, $body, $timestamp, $nonce), $secret, true));
    }

    public function verifyRequest(Request $request, string $secret, int $toleranceSeconds = 300): bool
    {
        $timestamp = (string) $request->header('X-Titan-Timestamp', '');
        $nonce = (string) $request->header('X-Titan-Nonce', '');
        $signature = (string) $request->header('X-Titan-Signature', '');

        if ($timestamp === '' || $nonce === '' || $signature === '' || ! ctype_digit($timestamp)) {
            return false;
        }

        if (abs(now()->timestamp - (int) $timestamp) > $toleranceSeconds) {
            return false;
        }

        $expected = $this->sign(
            $request->method(),
            '/' . ltrim($request->path(), '/'),
            (string) $request->getContent(),
            $timestamp,
            $nonce,
            $secret,
        );

        return hash_equals($expected, $signature);
    }

    private function canonical(string $method, string $path, string $body, string $timestamp, string $nonce): string
    {
        return implode("\n", [
            $timestamp,
            $nonce,
            strtoupper($method),
            '/' . ltrim($path, '/'),
            hash('sha256', $body),
        ]);
    }
}
