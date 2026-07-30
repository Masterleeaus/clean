<?php

declare(strict_types=1);

namespace App\Titan\Creative\Actions;

use App\Titan\Creative\Contracts\CreativeRepository;
use App\Titan\Tenancy\ActiveCompanyContext;

final class CreateContentAction
{
    public function __construct(private readonly CreativeRepository $repository, private readonly ActiveCompanyContext $context) {}

    public function execute(array $input): array
    {
        return ['ok' => true, 'data' => $this->repository->createContent($input, $this->context->companyId, $this->context->userId)];
    }
}
