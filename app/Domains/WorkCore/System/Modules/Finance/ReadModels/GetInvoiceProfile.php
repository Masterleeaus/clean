<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\ReadModels;

use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use InvalidArgumentException;

final class GetInvoiceProfile
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): array
    {
        $publicId = (string) ($filters['invoice_public_id'] ?? ''); if ($publicId === '') throw new InvalidArgumentException('Invoice is required.'); return $this->repository->invoiceProfile($publicId, $companyId);
    }
}
