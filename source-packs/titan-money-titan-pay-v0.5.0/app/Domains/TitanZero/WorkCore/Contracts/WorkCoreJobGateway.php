<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\WorkCore\Contracts;

use App\Domains\Company\Models\Company;
use App\Domains\TitanZero\WorkCore\DTO\CompletedBillableJob;
use App\Domains\TitanZero\WorkCore\DTO\WorkCoreJobResolution;
use App\Models\User;

interface WorkCoreJobGateway
{
    public function resolve(Company $company, string $query): WorkCoreJobResolution;

    public function complete(Company $company, User $actor, string $publicId, string $correlationId): CompletedBillableJob;
}
