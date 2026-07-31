<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Extensions\TitanMapsIntelligence\Contracts\SecretResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Vault\Vault;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class MapsCredentialResolver implements SecretResolver
{
    public function __construct(
        private readonly Vault $vault,
        private readonly Application $app,
    ) {}

    public function resolve(string $credentialReference): string
    {
        $reference = trim($credentialReference);
        if ($reference === '') {
            throw new RuntimeException('A Titan Vault credential reference is required.');
        }

        $query = DB::table('maps_provider_connections')
            ->where('credential_reference', $reference)
            ->where('enabled', true);

        $context = $this->app->bound(ActiveCompanyContext::class)
            ? $this->app->make(ActiveCompanyContext::class)
            : null;
        if ($context instanceof ActiveCompanyContext) {
            $query->where('company_id', (string) $context->companyId);
        }

        $connection = $query->first(['company_id']);
        if ($connection === null || ! is_numeric($connection->company_id)) {
            throw new RuntimeException('The Maps provider credential is not configured in the authorised company scope.');
        }

        return $this->vault->resolve(
            companyId: (int) $connection->company_id,
            reference: $reference,
            userId: $context?->userId,
        );
    }
}
