<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Actions;

use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Tenancy\ActiveCompanyContext;

final class CreateOnboardingFlowAction
{
    public function __construct(private readonly IntelligenceRepository $repository, private readonly ActiveCompanyContext $context) {}

    public function execute(array $input): array
    {
        return ['ok' => true, 'data' => $this->repository->createOnboardingFlow($input, $this->context->companyId, $this->context->userId)];
    }
}
