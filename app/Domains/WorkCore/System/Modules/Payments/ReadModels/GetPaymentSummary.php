<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\ReadModels;

use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use InvalidArgumentException;

final class GetPaymentSummary
{
    public function __construct(private PaymentRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): array
    {
        return $this->repository->paymentSummary($companyId, $filters);
    }
}
