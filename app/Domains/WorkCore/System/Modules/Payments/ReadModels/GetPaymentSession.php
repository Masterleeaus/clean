<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\ReadModels;

use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use InvalidArgumentException;

final class GetPaymentSession
{
    public function __construct(private PaymentRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): array
    {
        $publicId = (string) ($filters['session_public_id'] ?? ''); if ($publicId === '') throw new InvalidArgumentException('Payment session is required.'); return $this->repository->paymentSession($publicId, $companyId);
    }
}
