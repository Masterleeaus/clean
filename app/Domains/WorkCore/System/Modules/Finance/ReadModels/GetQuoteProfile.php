<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\ReadModels;

use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use InvalidArgumentException;

final class GetQuoteProfile
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): array
    {
        $publicId = (string) ($filters['quote_public_id'] ?? ''); if ($publicId === '') throw new InvalidArgumentException('Quote is required.'); return $this->repository->quoteProfile($publicId, $companyId);
    }
}
