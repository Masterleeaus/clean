<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Support;

use App\Domains\TitanPay\Contracts\SecretResolver;
use RuntimeException;

final class EnvironmentSecretResolver implements SecretResolver
{
    public function resolve(string $reference): array
    {
        if (! str_starts_with($reference, 'env:')) {
            throw new RuntimeException('A Titan Vault secret resolver is required for non-environment secret references.');
        }

        $prefix = substr($reference, 4);
        $configured = config('titanpay.secrets.'.$prefix);
        if (is_array($configured) && $configured !== []) {
            return array_filter($configured, static fn ($value): bool => $value !== null && $value !== '');
        }

        $json = $this->environment($prefix.'_JSON');
        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        }

        return array_filter([
            'secret_key' => $this->environment($prefix.'_SECRET') ?: $this->environment($prefix.'_SECRET_KEY'),
            'publishable_key' => $this->environment($prefix.'_PUBLISHABLE_KEY'),
            'webhook_secret' => $this->environment($prefix.'_WEBHOOK_SECRET'),
            'client_id' => $this->environment($prefix.'_CLIENT_ID'),
            'client_secret' => $this->environment($prefix.'_CLIENT_SECRET'),
            'webhook_id' => $this->environment($prefix.'_WEBHOOK_ID'),
        ], static fn ($value): bool => $value !== null && $value !== '');
    }

    private function environment(string $key): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $value = getenv($key);
        return $value === false ? null : $value;
    }
}
