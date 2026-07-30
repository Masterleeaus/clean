<?php

declare(strict_types=1);

namespace App\Titan\Vault;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class DatabaseVault implements Vault
{
    public function put(
        int $companyId,
        string $provider,
        string $key,
        string $value,
        ?int $userId = null,
        array $metadata = [],
    ): string {
        if ($companyId < 1 || trim($provider) === '' || trim($key) === '' || $value === '') {
            throw new \InvalidArgumentException('Vault company, provider, key and value are required.');
        }

        $reference = (string) Str::uuid();
        $now = now();

        DB::table('titan_vault_secrets')->insert([
            'reference' => $reference,
            'company_id' => $companyId,
            'user_id' => $userId,
            'provider' => trim($provider),
            'secret_key' => trim($key),
            'encrypted_value' => Crypt::encryptString($value),
            'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $reference;
    }

    public function resolve(int $companyId, string $reference, ?int $userId = null): string
    {
        $query = DB::table('titan_vault_secrets')
            ->where('company_id', $companyId)
            ->where('reference', $reference);

        if ($userId !== null) {
            $query->where(static function ($scope) use ($userId): void {
                $scope->whereNull('user_id')->orWhere('user_id', $userId);
            });
        }

        $encrypted = $query->value('encrypted_value');
        if (! is_string($encrypted) || $encrypted === '') {
            throw new RuntimeException('The requested Vault secret does not exist in the authorised scope.');
        }

        return Crypt::decryptString($encrypted);
    }

    public function forget(int $companyId, string $reference, ?int $userId = null): bool
    {
        $query = DB::table('titan_vault_secrets')
            ->where('company_id', $companyId)
            ->where('reference', $reference);

        if ($userId !== null) {
            $query->where(static function ($scope) use ($userId): void {
                $scope->whereNull('user_id')->orWhere('user_id', $userId);
            });
        }

        return $query->delete() > 0;
    }
}
