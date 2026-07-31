<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Actions;

use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Vault\Vault;
use InvalidArgumentException;

final class CreateVoiceProfileAction
{
    public function __construct(private readonly IntelligenceRepository $repository, private readonly ActiveCompanyContext $context, private readonly Vault $vault) {}
    public function execute(array $input): array
    {
        $key = trim((string) ($input['provider_key'] ?? ''));
        $definitions = (array) config('titan_intelligence.voice_providers', []);
        if ($key === '' || ! isset($definitions[$key])) throw new InvalidArgumentException('Voice provider is not registered.');
        $reference = trim((string) ($input['credential_reference'] ?? ''));
        if ($reference !== '') $this->vault->resolve($this->context->companyId, $reference, $this->context->userId);
        return ['ok' => true, 'data' => $this->repository->createVoiceProfile($input, $this->context->companyId, $this->context->userId)];
    }
}
