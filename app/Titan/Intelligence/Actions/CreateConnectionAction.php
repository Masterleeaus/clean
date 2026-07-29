<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Actions;

use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Vault\Vault;
use InvalidArgumentException;

final class CreateConnectionAction
{
    public function __construct(private readonly IntelligenceRepository $repository, private readonly ActiveCompanyContext $context, private readonly Vault $vault) {}

    public function execute(array $input): array
    {
        $key = trim((string) ($input['connector_key'] ?? ''));
        $definitions = (array) config('titan_intelligence.connectors', []);
        if ($key === '' || ! isset($definitions[$key])) throw new InvalidArgumentException('Connector is not registered.');
        $reference = trim((string) ($input['credential_reference'] ?? ''));
        if ($reference === '') throw new InvalidArgumentException('A Titan Vault credential reference is required.');
        $this->vault->resolve($this->context->companyId, $reference, $this->context->userId);
        if (isset($input['token_reference']) && trim((string) $input['token_reference']) !== '') {
            $this->vault->resolve($this->context->companyId, (string) $input['token_reference'], $this->context->userId);
        }
        return ['ok' => true, 'data' => $this->repository->createConnection($input, $this->context->companyId, $this->context->userId)];
    }
}
