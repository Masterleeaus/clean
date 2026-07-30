<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\ReadModels;

use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use InvalidArgumentException;

final class SearchReceivables
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): array
    {
        $filters['limit'] = min(100, max(1, $perPage)); return $this->repository->receivables($companyId, $filters);
    }
}
