<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Actions;

use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Tenancy\ActiveCompanyContext;

final class UpsertModelAction
{
    public function __construct(private readonly IntelligenceRepository $repository, private readonly ActiveCompanyContext $context) {}
    public function execute(array $input): array
    {
        $definitions = (array) config('titan_intelligence.providers', []);
        if ($definitions === []) throw new \RuntimeException('AI provider registry is unavailable.');
        return ['ok' => true, 'data' => $this->repository->upsertModel($input, $this->context->companyId, $this->context->userId)];
    }
}
