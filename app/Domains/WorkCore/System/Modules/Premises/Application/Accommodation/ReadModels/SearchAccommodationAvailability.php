<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Application\Accommodation\ReadModels;

use App\Domains\WorkCore\System\Modules\Premises\Contracts\PropertyAccommodationRepositoryContract;
use InvalidArgumentException;

final class SearchAccommodationAvailability
{
    public function __construct(private PropertyAccommodationRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): mixed
    {
        if ($companyId < 1) { throw new InvalidArgumentException('A valid company is required.'); }
        return $this->repository->availability($companyId, (string) ($filters['arrival_at'] ?? ''), (string) ($filters['departure_at'] ?? ''), max(1, (int) ($filters['capacity'] ?? 1)), isset($filters['premises_public_id']) ? (string) $filters['premises_public_id'] : null);
    }
}
