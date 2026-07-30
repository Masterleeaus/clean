<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Domain;

final class ShareToken
{
    /** @param callable(int):string|null $generator @return array{token:string,hash:string} */
    public static function issue(?callable $generator = null): array
    {
        $token = $generator !== null
            ? $generator(64)
            : bin2hex(random_bytes(32));
        if (strlen($token) < 32) throw new \RuntimeException('Share token generator returned insufficient entropy.');

        return ['token' => $token, 'hash' => hash('sha256', $token)];
    }

    public static function verify(string $token, string $hash): bool
    {
        return hash_equals($hash, hash('sha256', $token));
    }
}
