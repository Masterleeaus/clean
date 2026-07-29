<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\ReadModels;

use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use InvalidArgumentException;

final class GetFinanceSummary
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): array
    {
        return $this->repository->financeSummary($companyId, $filters);
    }
}
