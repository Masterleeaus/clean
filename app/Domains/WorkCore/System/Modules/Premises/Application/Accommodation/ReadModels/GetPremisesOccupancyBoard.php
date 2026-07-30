<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Application\Accommodation\ReadModels;

use App\Domains\WorkCore\System\Modules\Premises\Contracts\PropertyAccommodationRepositoryContract;
use InvalidArgumentException;

final class GetPremisesOccupancyBoard
{
    public function __construct(private PropertyAccommodationRepositoryContract $repository) {}

    public function __invoke(array $filters, int $companyId, int $perPage = 25): mixed
    {
        if ($companyId < 1) { throw new InvalidArgumentException('A valid company is required.'); }
        return $this->repository->occupancyBoard($companyId, $filters);
    }
}
